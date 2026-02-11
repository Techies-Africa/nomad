<?php

namespace TechiesAfrica\Nomad\Casts;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use TechiesAfrica\Nomad\Services\Timezone\NomadTimezoneResolver;

class NomadDatetimeCast implements CastsAttributes
{
    /**
     * Cast the given value from the database to the user's timezone.
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        $resolver = app(NomadTimezoneResolver::class);
        $userTz = $resolver->getTimezone();

        if ($value instanceof CarbonInterface) {
            return $value->copy()->setTimezone($userTz);
        }

        return Carbon::parse($value, 'UTC')->setTimezone($userTz);
    }

    /**
     * Prepare the given value for storage by converting to UTC.
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof CarbonInterface) {
            return $value->copy()->setTimezone('UTC')->format($model->getDateFormat());
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->setTimezone('UTC')->format($model->getDateFormat());
        }

        // String values pass through as-is (assumed to be in UTC already)
        return $value;
    }
}

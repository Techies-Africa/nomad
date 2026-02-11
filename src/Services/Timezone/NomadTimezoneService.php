<?php

namespace TechiesAfrica\Nomad\Services\Timezone;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NomadTimezoneService
{
    protected ?string $timezone = null;
    protected ?int $userId = null;

    /**
     * Set the timezone value.
     */
    public function setTimezone(string $timezone): static
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * Set the user ID explicitly.
     */
    public function setUser(int $userId): static
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * Save the timezone to the database.
     *
     * @return int|false Number of affected rows, or false if no user available.
     * @throws ValidationException
     */
    public function save(): int|false
    {
        $data = $this->validate();
        $userId = $this->resolveUserId();

        if ($userId === null) {
            return false;
        }

        $table = Config::get('nomad.table', 'users');
        $column = Config::get('nomad.column', 'timezone');

        return DB::table($table)
            ->where('id', $userId)
            ->update([$column => $data['timezone']]);
    }

    /**
     * Validate the timezone is a valid IANA identifier.
     *
     * @throws ValidationException
     */
    protected function validate(): array
    {
        return Validator::make(
            ['timezone' => $this->timezone],
            ['timezone' => ['required', 'string', 'timezone']]
        )->validate();
    }

    /**
     * Resolve the user ID from explicit set or current auth.
     */
    protected function resolveUserId(): ?int
    {
        if ($this->userId !== null) {
            return $this->userId;
        }

        $guard = Config::get('nomad.guard');

        try {
            return auth($guard)->id();
        } catch (\Throwable) {
            return null;
        }
    }
}

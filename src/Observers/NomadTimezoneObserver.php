<?php

namespace TechiesAfrica\Nomad\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use TechiesAfrica\Nomad\Casts\NomadDatetimeCast;
use TechiesAfrica\Nomad\Services\Timezone\NomadTimezoneResolver;

class NomadTimezoneObserver
{
    protected NomadTimezoneResolver $resolver;

    /**
     * Cache of date column names per model class.
     */
    protected static array $dateColumnCache = [];

    public function __construct(NomadTimezoneResolver $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * Register wildcard event listeners for all Eloquent models.
     */
    public function register(): void
    {
        Event::listen('eloquent.retrieved: *', [$this, 'handleModelLoaded']);
        Event::listen('eloquent.created: *', [$this, 'handleModelLoaded']);
    }

    /**
     * Apply timezone casts when a model is loaded from the database or created.
     */
    public function handleModelLoaded(string $event, array $models): void
    {
        $model = $models[0];

        if ($this->shouldSkip($model)) {
            return;
        }

        $this->applyCasts($model);
    }

    /**
     * Dynamically merge NomadDatetimeCast onto all datetime columns.
     */
    protected function applyCasts(Model $model): void
    {
        $dateColumns = $this->getDateColumns($model);

        if (empty($dateColumns)) {
            return;
        }

        $casts = [];
        foreach ($dateColumns as $col) {
            $casts[$col] = NomadDatetimeCast::class;
        }

        $model->mergeCasts($casts);
    }

    /**
     * Determine if a model should be skipped from timezone conversion.
     */
    protected function shouldSkip(Model $model): bool
    {
        if (!Config::get('nomad.enabled', true)) {
            return true;
        }

        $excluded = Config::get('nomad.excluded_models', []);

        return in_array(get_class($model), $excluded, true);
    }

    /**
     * Get datetime column names for a model, with caching per model class.
     *
     * Combines the model's getDates() (timestamps + $dates property)
     * with columns explicitly cast to datetime types in $casts.
     */
    protected function getDateColumns(Model $model): array
    {
        $class = get_class($model);

        if (isset(static::$dateColumnCache[$class])) {
            return static::$dateColumnCache[$class];
        }

        $dateColumns = $model->getDates();

        $dateCastTypes = ['date', 'datetime', 'immutable_date', 'immutable_datetime'];

        foreach ($model->getCasts() as $column => $castType) {
            // Handle format suffixes like "datetime:Y-m-d"
            $baseCastType = is_string($castType) ? explode(':', $castType, 2)[0] : '';
            if (in_array($baseCastType, $dateCastTypes, true)
                && !in_array($column, $dateColumns, true)) {
                $dateColumns[] = $column;
            }
        }

        return static::$dateColumnCache[$class] = $dateColumns;
    }

    /**
     * Clear the date column cache (useful in tests).
     */
    public static function clearCache(): void
    {
        static::$dateColumnCache = [];
    }
}

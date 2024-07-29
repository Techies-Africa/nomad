<?php

namespace Src\Traits;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

trait NomadTimezoneTrait
{
    protected $model;
    protected static function bootTimezoneTrait()
    {
        $guard = Config::get("nomad.guard");
        static::saving(function (Model $model) use ($guard) {
            $user_timezone = auth($guard)->user()->timezone ?? 'UTC';

            // Convert all datetime attributes to UTC before saving if they are dirty
            foreach (self::fetchColumns($model) ?? [] as $date_attribute) {
                if ($model->isDirty($date_attribute) && !empty($model->$date_attribute)) {
                    $model->$date_attribute = Carbon::parse($model->$date_attribute, $user_timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
                }
            }
        });
    }

    public static function fetchColumns(Model $model)
    {
        $table_name = $model->getTable();

        $columns = Schema::getColumnListing($table_name);
        $date_columns = [];

        foreach ($columns as $column) {
            $column_type = Schema::getColumnType($table_name, $column);
            if (in_array($column_type, ['datetime', 'timestamp'])) {
                $date_columns[] = $column;
            }
        }

        return $date_columns;
    }

    public function getAttribute($key)
    {
        if (in_array(get_class($this), Config::get("nomad.excluded_models"))) {
            return parent::getAttribute($key);
        }

        $value = parent::getAttribute($key);

        if ($value instanceof \DateTimeInterface) {
            $guard = Config::get("nomad.guard");
            $user_timezone = auth($guard)->user()->timezone ?? 'UTC';
            return Carbon::instance($value)->setTimezone($user_timezone);
        }

        return $value;
    }
}

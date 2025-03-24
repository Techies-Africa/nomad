<?php

namespace TechiesAfrica\Nomad\Helper;
use Illuminate\Support\Str;


class StaticHelper
{
    static function getModelFromTable(string $table_name): string
    {
        foreach (get_declared_classes() as $class) {
            if (is_subclass_of($class, \Illuminate\Database\Eloquent\Model::class)) {
                $model_instance = new $class;
                if ($model_instance->getTable() === $table_name) {
                    $model_class = $class;
                }
            }
        }

        if (empty($model_class)) {
            return "App\\Models\\" . ucfirst(Str::camel(Str::singular($table_name)));
        }

        return $model_class;
    }

    static function getModelName($table_name)
    {
        return ucfirst(Str::camel(Str::singular($table_name)));
    }
}

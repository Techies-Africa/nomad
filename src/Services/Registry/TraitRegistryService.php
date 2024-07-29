<?php

namespace TechiesAfrica\Nomad\Services\Registry;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;

class TraitRegistryService
{
    protected $models;
    public function __construct()
    {
        $this->fetchModels();
    }

    public function fetchModels()
    {
        $model_path = app_path('Models');
        $models = File::allFiles($model_path);
        $this->models = $models;
    }

    public function apply()
    {
        foreach ($this->models as $model) {
            $model_class = 'App\\Models\\' . $model->getFilenameWithoutExtension();
            if (class_exists($model_class) && is_subclass_of($model_class, Model::class)) {
                $this->configureEvent($model_class);
            }
        }
    }

    public function configureEvent($model_class)
    {
        Event::listen("eloquent.booted: {$model_class}", function ($model) {
            $this->syncToModel($model);
        });
    }

    public function syncToModel($model)
    {
        $trait = 'Src\Traits\NomadTimezoneTrait';

        if (!in_array($trait, class_uses($model))) {
            $model->extend(function ($model) use ($trait) {
                $model->implement($trait);
            });
        }
    }
}

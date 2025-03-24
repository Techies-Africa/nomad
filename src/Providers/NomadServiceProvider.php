<?php

namespace TechiesAfrica\Nomad\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use TechiesAfrica\Nomad\Console\Commands\General\InstallCommand;
use TechiesAfrica\Nomad\Console\Commands\Database\MigrateCommand;
use TechiesAfrica\Nomad\Console\Commands\General\UninstallCommand;
use TechiesAfrica\Nomad\Services\Registry\TraitRegistryService;

class NomadServiceProvider extends ServiceProvider
{
    /**
     * Register application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/nomad.php', 'nomad');
    }

    /**
     * Bootstrap application services.
     *
     * @return void
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->setupCommands();
        }

        $this->launchPublisher();
        $this->loadRoutes();
        $this->loadTimezoneTrait();
    }

    public function launchPublisher()
    {
        $this->publishes([
            __DIR__ . '/../config/nomad.php' => config_path('nomad.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../Middleware/NomadMiddleware.php' => app_path("Http/Middleware/Nomad/NomadMiddleware.php"),
        ], 'middleware');

        $this->publishes([
            __DIR__ . '/../database/migrations/create_timezone_column.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_timezone_column.php'),
        ], 'migrations');
    }

    public function loadRoutes()
    {
        Route::group($this->routeConfiguration(), function () {
            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        });
    }

    public function routeConfiguration()
    {
        return [
            'prefix' => config('nomad.prefix'),
            'middleware' => config('nomad.middleware', 'web'),
        ];
    }


    public function setupCommands()
    {
        $this->commands([
            InstallCommand::class,
            UninstallCommand::class,
            MigrateCommand::class
        ]);
    }

    public function loadTimezoneTrait()
    {
        (new TraitRegistryService())->apply();
    }
}

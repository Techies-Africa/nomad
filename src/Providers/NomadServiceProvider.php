<?php

namespace TechiesAfrica\Nomad\Providers;

use Illuminate\Support\ServiceProvider;
use TechiesAfrica\Nomad\Console\Commands\Database\MigrateCommand;
use TechiesAfrica\Nomad\Console\Commands\General\InstallCommand;
use TechiesAfrica\Nomad\Console\Commands\General\UninstallCommand;
use TechiesAfrica\Nomad\Observers\NomadTimezoneObserver;
use TechiesAfrica\Nomad\Services\Timezone\NomadTimezoneResolver;

class NomadServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/nomad.php', 'nomad');

        $this->app->singleton(NomadTimezoneResolver::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->setupCommands();
        }

        $this->launchPublisher();

        if (config('nomad.enabled', true)) {
            $this->app->make(NomadTimezoneObserver::class)->register();
        }
    }

    protected function launchPublisher(): void
    {
        $this->publishes([
            __DIR__ . '/../config/nomad.php' => config_path('nomad.php'),
        ], 'nomad-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/create_timezone_column.php.stub' => database_path(
                'migrations/' . date('Y_m_d_His', time()) . '_create_timezone_column.php'
            ),
        ], 'nomad-migrations');

        $this->publishes([
            __DIR__ . '/../Stubs/NomadMiddleware.stub' => app_path('Http/Middleware/Nomad/NomadMiddleware.php'),
        ], 'nomad-middleware');
    }

    protected function setupCommands(): void
    {
        $this->commands([
            InstallCommand::class,
            UninstallCommand::class,
            MigrateCommand::class,
        ]);
    }
}

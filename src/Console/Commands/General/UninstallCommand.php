<?php

namespace TechiesAfrica\Nomad\Console\Commands\General;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UninstallCommand extends Command
{
    protected $signature = 'nomad:uninstall';
    protected $description = 'Uninstall Nomad from the application';

    public function handle(): void
    {
        $this->info('Uninstalling Nomad...');

        // Remove config file
        $configPath = config_path('nomad.php');
        if (File::exists($configPath)) {
            File::delete($configPath);
            $this->line('Deleted config/nomad.php');
        }

        // Remove middleware file
        $middlewarePath = app_path('Http/Middleware/Nomad/NomadMiddleware.php');
        if (File::exists($middlewarePath)) {
            File::delete($middlewarePath);
            $this->line('Deleted Http/Middleware/Nomad/NomadMiddleware.php');

            // Remove directory if empty
            $middlewareDir = app_path('Http/Middleware/Nomad');
            if (File::isDirectory($middlewareDir) && empty(File::files($middlewareDir))) {
                File::deleteDirectory($middlewareDir);
            }
        }

        // Remove migration file(s) using glob pattern
        $migrationFiles = glob(database_path('migrations/*_create_timezone_column.php'));
        foreach ($migrationFiles as $file) {
            File::delete($file);
            $this->line('Deleted ' . basename($file));
        }

        $this->info('Nomad uninstalled successfully.');
    }
}

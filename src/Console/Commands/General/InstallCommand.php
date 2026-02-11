<?php

namespace TechiesAfrica\Nomad\Console\Commands\General;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'nomad:install';
    protected $description = 'Setup Nomad in an application';

    public function handle(): void
    {
        $this->info('Installing Nomad...');

        $this->publishConfig();
        $this->publishMiddleware();
        $this->publishMigration();

        $this->info('Nomad installed successfully.');
    }

    private function publishConfig(): void
    {
        $this->info('Publishing configuration...');

        if (!File::exists(config_path('nomad.php'))) {
            $this->publishFile('nomad-config');
            $this->info('Published configuration');
        } else {
            if ($this->shouldOverwrite('Config file already exists. Do you want to overwrite it?')) {
                $this->info('Overwriting configuration file...');
                $this->publishFile('nomad-config', true);
            } else {
                $this->info('Existing configuration was not overwritten');
            }
        }
    }

    private function publishMiddleware(): void
    {
        $middlewareDir = app_path('Http/Middleware/Nomad');
        $middlewareFile = $middlewareDir . '/NomadMiddleware.php';

        if (!File::exists($middlewareFile)) {
            $this->generateMiddleware($middlewareDir, $middlewareFile);
        } else {
            if ($this->shouldOverwrite('Middleware file already exists. Do you want to overwrite it?')) {
                $this->info('Overwriting middleware file...');
                $this->generateMiddleware($middlewareDir, $middlewareFile);
            } else {
                $this->info('Existing middleware was not overwritten');
            }
        }
    }

    private function generateMiddleware(string $middlewareDir, string $middlewareFile): void
    {
        if (!file_exists($middlewareDir)) {
            mkdir($middlewareDir, 0755, true);
        }

        $stubPath = realpath(__DIR__ . '/../../../Stubs/NomadMiddleware.stub');
        $stub = file_get_contents($stubPath);

        $content = str_replace('{{ namespace }}', 'App\\Http\\Middleware\\Nomad', $stub);
        file_put_contents($middlewareFile, $content);

        $this->info('Middleware published to: ' . $middlewareFile);
    }

    private function publishMigration(): void
    {
        $existing = glob(database_path('migrations/*_create_timezone_column.php'));

        if (empty($existing)) {
            $this->publishFile('nomad-migrations');
            $this->info('Published migration');
        } else {
            if ($this->shouldOverwrite('Migration file already exists. Do you want to overwrite it?')) {
                $this->info('Overwriting migration file...');
                $this->publishFile('nomad-migrations', true);
            } else {
                $this->info('Existing migration was not overwritten');
            }
        }
    }

    private function shouldOverwrite(string $message): bool
    {
        return $this->confirm($message, false);
    }

    private function publishFile(string $tag, bool $forcePublish = false): void
    {
        $params = [
            '--provider' => 'TechiesAfrica\\Nomad\\Providers\\NomadServiceProvider',
            '--tag' => $tag,
        ];

        if ($forcePublish) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }
}

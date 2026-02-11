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
        $this->publishMigration();

        $this->newLine();
        $this->info('Nomad installed successfully.');
    }

    private function publishConfig(): void
    {
        if (!File::exists(config_path('nomad.php'))) {
            $this->publishFile('nomad-config');
            $this->info('Published configuration');
        } else {
            if ($this->shouldOverwrite('Config file already exists. Do you want to overwrite it?')) {
                $this->publishFile('nomad-config', true);
                $this->info('Overwritten configuration');
            } else {
                $this->info('Existing configuration was not overwritten');
            }
        }
    }

    private function publishMigration(): void
    {
        $existing = glob(database_path('migrations/*_create_timezone_column.php'));

        if (empty($existing)) {
            $this->publishFile('nomad-migrations');
            $this->info('Published migration');
        } else {
            if ($this->shouldOverwrite('Migration file already exists. Do you want to overwrite it?')) {
                $this->publishFile('nomad-migrations', true);
                $this->info('Overwritten migration');
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

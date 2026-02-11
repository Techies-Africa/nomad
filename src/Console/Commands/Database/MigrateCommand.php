<?php

namespace TechiesAfrica\Nomad\Console\Commands\Database;

use Illuminate\Console\Command;

class MigrateCommand extends Command
{
    protected $signature = 'nomad:migrate';
    protected $description = 'Publish and run the Nomad timezone column migration';

    public function handle(): void
    {
        $this->info('Running Nomad Migration...');

        // Publish migration if not already published
        $existing = glob(database_path('migrations/*_create_timezone_column.php'));

        if (empty($existing)) {
            $this->call('vendor:publish', [
                '--provider' => 'TechiesAfrica\\Nomad\\Providers\\NomadServiceProvider',
                '--tag' => 'nomad-migrations',
            ]);
            $existing = glob(database_path('migrations/*_create_timezone_column.php'));
        }

        // Run the migration
        if (!empty($existing)) {
            $this->call('migrate', [
                '--path' => 'database/migrations/' . basename($existing[0]),
            ]);
        }

        $this->info('Nomad Migration Successful');
    }
}

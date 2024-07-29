<?php

namespace TechiesAfrica\Nomad\Console\Commands\Database;

use Illuminate\Console\Command;

class MigrateCommand extends Command
{
    protected $signature = 'nomad:migrate';

    protected $description = 'Setup timezone column in the selected table';

    public function handle()
    {
        $this->info('Running Nomad Migration...');

        if (!class_exists('CreateTimezoneColumn')) {
            $this->publishFile('migrations');
        }

        $this->info('Nomad Migration Successful');
    }

    private function publishFile($tag, $forcePublish = false)
    {
        $params = [
            '--provider' => "TechiesAfrica\Nomad\Providers\NomadServiceProvider",
            '--tag' => $tag
        ];

        if ($forcePublish === true) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }

}

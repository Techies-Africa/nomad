<?php

namespace TechiesAfrica\Nomad\Console\Commands\General;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class InstallCommand extends Command
{
    protected $signature = 'nomad:install';

    protected $description = 'Setup Nomad in an application';

    public function handle()
    {
        $this->info('Installing Nomad...');

        $this->info('Publishing configuration...');

        if (!$this->configExists('nomad.php')) {
            $this->publishFile("config");
            $this->info('Published configuration');
        } else {
            if ($this->shouldOverwriteFile('Config file already exists. Do you want to overwrite it?')) {
                $this->info('Overwriting configuration file...');
                $this->publishFile("config", true);
            } else {
                $this->info('Existing configuration was not overwritten');
            }
        }


        if (!$this->fileExists(app_path("Http/Middleware/Nomad/NomadMiddleware.php"))) {
            $this->publishFile("middleware");
        } else {
            if ($this->shouldOverwriteFile('Middleware file already exists. Do you want to overwrite it?')) {
                $this->info('Overwriting middleware file...');
                $this->publishFile("middleware", true);
            } else {
                $this->info('Existing middleware was not overwritten');
            }
        }

        $this->info('Installed Nomad');
    }

    private function configExists($fileName)
    {
        return File::exists(config_path($fileName));
    }

    private function fileExists($file_path)
    {
        return File::exists($file_path);
    }

    private function shouldOverwriteFile($message)
    {
        return $this->confirm(
            $message,
            false
        );
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

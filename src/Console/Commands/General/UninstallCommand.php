<?php

namespace TechiesAfrica\Nomad\Console\Commands\General;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UninstallCommand extends Command
{
    protected $signature = 'nomad:uninstall';

    protected $description = 'Unistall Nomad in an application';

    public function handle()
    {
        $this->info('Uninstalling Nomad...');

        $this->info('Uninstalling Nomad from application...');

        if ($this->configExists("nomad.php")) {
            if(File::delete(config_path("nomad.php"))){
                $this->line("config/nomad.php file deleted successfully.");
            }
        }

        if ($this->fileExists($path = app_path("Http/Middleware/Nomad/NomadMiddleware.php"))) {
            if(File::delete($path)){
                $this->line("Http/Middleware/Nomad/NomadMiddleware.php file deleted successfully.");
            }
        }

        if ($path = database_path('migrations/' . date('Y_m_d_His', time()) . '_create_timezone_column.php')) {
            if(File::delete($path)){
                $this->line("migrations/" . date('Y_m_d_His', time()) . "_create_timezone_column.php file deleted successfully.");
            }
        }

        $this->info("Nomad uninstalled sucessfully...");
    }

    private function configExists($fileName)
    {
        return File::exists(config_path($fileName));
    }

    private function fileExists($file_path)
    {
        return File::exists($file_path);
    }
}

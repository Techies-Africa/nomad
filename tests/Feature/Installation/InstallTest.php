<?php

namespace TechiesAfrica\Nomad\Tests\Feature\Installation;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use TechiesAfrica\Nomad\Tests\TestCase;

class InstallTest extends TestCase
{
    public function test_install_command(): void
    {
        $configFile = config_path('nomad.php');

        // Clean slate
        Artisan::call('nomad:uninstall');

        $this->assertFalse(File::exists($configFile));
        $this->assertEmpty(glob(database_path('migrations/*_create_timezone_column.php')));

        Artisan::call('nomad:install');

        $this->assertTrue(File::exists($configFile));
        $this->assertNotEmpty(glob(database_path('migrations/*_create_timezone_column.php')));
    }

    public function test_uninstall_command(): void
    {
        $configFile = config_path('nomad.php');

        // Clean slate then install fresh
        Artisan::call('nomad:uninstall');
        Artisan::call('nomad:install');

        Artisan::call('nomad:uninstall');

        $this->assertFalse(File::exists($configFile));
        $this->assertEmpty(glob(database_path('migrations/*_create_timezone_column.php')));
    }
}

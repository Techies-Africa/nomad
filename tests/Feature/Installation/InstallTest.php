<?php

namespace TechiesAfrica\Nomad\Tests\Feature\Installation;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use TechiesAfrica\Nomad\Tests\TestCase;

class InstallTest extends TestCase
{
    function test_install_command()
    {
        $config_file = config_path('nomad.php');
        $middleware_path = app_path('Http/Middleware/Nomad/NomadMiddleware.php');
        $migration_path = database_path('migrations/' . date('Y_m_d_His', time()) . '_create_timezone_column.php');

        Artisan::call("nomad:uninstall");

        $this->assertFalse(File::exists($config_file));
        $this->assertFalse(File::exists($middleware_path));
        $this->assertFalse(File::exists($migration_path));

        Artisan::call("nomad:install");

        $this->assertTrue(File::exists($config_file));
        $this->assertTrue(File::exists($middleware_path));
        $this->assertTrue(File::exists($migration_path));
    }


    function test_reinstall_command()
    {
        $config_file = config_path('nomad.php');

        $this->assertTrue(File::exists($config_file));

        $command = $this->artisan('nomad:install');
        $command->expectsQuestion("Config file already exists. Do you want to overwrite it?", "yes");
        $command->expectsQuestion("Middleware file already exists. Do you want to overwrite it?", "yes");
        $command->execute();
        $command->expectsOutput('Overwriting configuration file...');

        $this->assertTrue(File::exists($config_file));

        $this->assertEquals(
            File::get($config_file),
            File::get(__DIR__.'/../../../src/config/nomad.php')
        );
    }

    function test_uninstall_command()
    {
        $config_file = config_path('nomad.php');
        $middleware_path = app_path('Http/Middleware/Nomad/NomadMiddleware.php');
        $migration_path = database_path('migrations/' . date('Y_m_d_His', time()) . '_create_timezone_column.php');

        Artisan::call("nomad:uninstall");

        $this->assertFalse(File::exists($config_file));
        $this->assertFalse(File::exists($middleware_path));
        $this->assertFalse(File::exists($migration_path));
    }
}

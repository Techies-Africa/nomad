<?php

namespace TechiesAfrica\Nomad\Tests\Feature\Migration;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use TechiesAfrica\Nomad\Tests\TestCase;

class MigrateTest extends TestCase
{
    function test_migration_command()
    {
        $migration_path = database_path('migrations/' . date('Y_m_d_His', time()) . '_create_timezone_column.php');

        if (File::exists($migration_path)) {
            File::delete($migration_path);
        }

        $this->assertFalse(File::exists($migration_path), 'Migration file already exists');

        Artisan::call("nomad:migrate");

        $this->assertTrue(File::exists($migration_path), 'Migration file was not published');
    }
}

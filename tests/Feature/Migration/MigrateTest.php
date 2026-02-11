<?php

namespace TechiesAfrica\Nomad\Tests\Feature\Migration;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use TechiesAfrica\Nomad\Tests\TestCase;

class MigrateTest extends TestCase
{
    public function test_migration_command_publishes_file(): void
    {
        // Clean up any existing migration file
        $existing = glob(database_path('migrations/*_create_timezone_column.php'));
        foreach ($existing as $file) {
            File::delete($file);
        }

        $this->assertEmpty(
            glob(database_path('migrations/*_create_timezone_column.php')),
            'Migration file already exists'
        );

        Artisan::call('nomad:migrate');

        $this->assertNotEmpty(
            glob(database_path('migrations/*_create_timezone_column.php')),
            'Migration file was not published'
        );
    }
}

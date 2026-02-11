<?php

namespace TechiesAfrica\Nomad\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use TechiesAfrica\Nomad\Providers\NomadServiceProvider;

class TestCase extends TestbenchTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app): array
    {
        return [
            NomadServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('app.timezone', 'UTC');

        $app['config']->set('nomad.enabled', true);
        $app['config']->set('nomad.table', 'users');
        $app['config']->set('nomad.column', 'timezone');
        $app['config']->set('nomad.guard', null);
        $app['config']->set('nomad.header', 'X-Timezone');
        $app['config']->set('nomad.session_key', 'nomad_timezone');
        $app['config']->set('nomad.geoip.enabled', false);
        $app['config']->set('nomad.excluded_models', []);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}

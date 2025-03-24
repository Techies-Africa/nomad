<?php

namespace TechiesAfrica\Nomad\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as TestbenchTestCase;
use TechiesAfrica\Nomad\Providers\NomadServiceProvider;
use Tests\CreatesApplication;

class TestCase extends TestbenchTestCase
{
    use RefreshDatabase;
    // use CreatesApplication;
    public function setUp(): void
    {
        parent::setUp();
        // additional setup
    }

    protected function getPackageProviders($app)
    {
        return [
            NomadServiceProvider::class,
        ];
    }

    // protected function getBasePath(): string
    // {
    //     return __DIR__ . '/../';
    // }

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}

<?php

namespace TechiesAfrica\Nomad\Tests;

use Orchestra\Testbench\TestCase as TestbenchTestCase;
use TechiesAfrica\Nomad\Providers\NomadServiceProvider;
use Tests\CreatesApplication;

class TestCase extends TestbenchTestCase
{
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

    protected function getEnvironmentSetUp($app)
    {
        // perform environment setup
    }
}

<?php

namespace MohammedManssour\FormRequestTester\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use MohammedManssour\FormRequestTester\Tests\Stubs\ServiceProviders\RouteServiceProvider;

class TestCase extends BaseTestCase
{
    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/Stubs/Database/migrations');
        $this->withFactories(__DIR__ . '/Stubs/Database/factories');

    // and other test setup steps you need to perform
    }

    protected function getPackageProviders($app)
    {
        return [RouteServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
    // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }
}
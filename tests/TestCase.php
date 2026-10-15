<?php

namespace Datalogix\BuilderMacros\Tests;

use Datalogix\BuilderMacros\BuilderMacroServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }

    protected function getPackageProviders($app): array
    {
        return [BuilderMacroServiceProvider::class];
    }
}

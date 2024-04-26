<?php

namespace Datalogix\BuilderMacros\Tests;

use Datalogix\BuilderMacros\BuilderMacroServiceProvider;
use GrahamCampbell\TestBench\AbstractPackageTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends AbstractPackageTestCase
{
    use RefreshDatabase;

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }

    protected static function getServiceProviderClass(): string
    {
        return BuilderMacroServiceProvider::class;
    }
}

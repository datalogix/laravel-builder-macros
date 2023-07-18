<?php

namespace Datalogix\BuilderMacros\Tests;

use Datalogix\BuilderMacros\BuilderMacroServiceProvider;
use GrahamCampbell\TestBench\AbstractPackageTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends AbstractPackageTestCase
{
    use RefreshDatabase;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }

    /**
     * Get the service provider class.
     */
    protected static function getServiceProviderClass(): string
    {
        return BuilderMacroServiceProvider::class;
    }
}

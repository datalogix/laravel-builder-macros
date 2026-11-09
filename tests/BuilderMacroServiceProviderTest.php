<?php

namespace Datalogix\BuilderMacros\Tests;

use Datalogix\BuilderMacros\BuilderMacroServiceProvider;
use Datalogix\BuilderMacros\Macros\Map;
use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Illuminate\Database\Eloquent\Builder;

class BuilderMacroServiceProviderTest extends TestCase
{
    public function test_registers_macros()
    {
        foreach (['addSubSelect', 'defaultSelectAll', 'filter', 'joinRelation', 'leftJoinRelation', 'map', 'search', 'whereLike'] as $macro) {
            $this->assertTrue(Builder::hasGlobalMacro($macro), "Macro [{$macro}] is not registered.");
        }
    }

    public function test_keeps_macro_already_registered()
    {
        Builder::macro('map', function () {
            return 'custom';
        });

        try {
            (new BuilderMacroServiceProvider($this->app))->register();

            // @phpstan-ignore arguments.count (the custom macro takes no arguments)
            $this->assertEquals('custom', User::query()->map());
        } finally {
            // Macros are static, so the package macro is restored for the next tests
            Builder::macro('map', app(Map::class)());
        }
    }
}

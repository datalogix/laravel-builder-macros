<?php

use Datalogix\BuilderMacros\BuilderMacroServiceProvider;
use Datalogix\BuilderMacros\Macros\Map;
use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Illuminate\Database\Eloquent\Builder;

test('registers macros', function () {
    foreach (['addSubSelect', 'defaultSelectAll', 'filter', 'joinRelation', 'leftJoinRelation', 'map', 'search', 'whereLike'] as $macro) {
        expect(Builder::hasGlobalMacro($macro))->toBeTrue("Macro [{$macro}] is not registered.");
    }
});

test('keeps macro already registered', function () {
    Builder::macro('map', fn () => 'custom');

    try {
        (new BuilderMacroServiceProvider(app()))->register();

        // @phpstan-ignore arguments.count (the custom macro takes no arguments)
        expect(User::query()->map())->toBe('custom');
    } finally {
        // Macros are static, so the package macro is restored for the next tests
        Builder::macro('map', app(Map::class)());
    }
});

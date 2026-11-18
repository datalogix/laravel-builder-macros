<?php

use Datalogix\BuilderMacros\Tests\Database\Models\User;

test('query is the same as where like', function () {
    $expected = User::whereLike(['name', 'posts.title'], 'foo', false, true, true);
    $actual = User::search(['name', 'posts.title'], 'foo', false, true, true);

    expect($actual->toSql())->toBe($expected->toSql())
        ->and($actual->getBindings())->toBe($expected->getBindings());
});

<?php

use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Illuminate\Support\Facades\DB;

test('query', function () {
    expect(User::addSubSelect('name', User::query())->toSql())
        ->toBe('select "users".*, (select * from "users" limit 1) as "name" from "users"');
});

test('query does not change sub query', function () {
    $subQuery = User::query();
    User::addSubSelect('name', $subQuery)->toSql();

    expect($subQuery->getQuery()->limit)->toBeNull();
});

test('query with base query builder', function () {
    expect(User::addSubSelect('name', DB::table('users'))->toSql())
        ->toBe('select "users".*, (select * from "users" limit 1) as "name" from "users"');
});

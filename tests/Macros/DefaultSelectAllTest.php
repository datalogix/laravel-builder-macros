<?php

use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Illuminate\Support\Facades\DB;

test('query without columns', function () {
    expect(User::defaultSelectAll()->toSql())->toBe('select "users".* from "users"');
});

test('query with one column', function () {
    expect(User::select('email')->defaultSelectAll()->toSql())->toBe('select "email" from "users"');
});

test('query with more columns', function () {
    expect(User::select('email', 'name')->defaultSelectAll()->toSql())->toBe('select "email", "name" from "users"');
});

test('query with table alias', function () {
    expect(User::from('users as u')->defaultSelectAll()->toSql())->toBe('select "u".* from "users" as "u"');
});

test('query with expression table', function () {
    expect(User::from(DB::raw('users'))->defaultSelectAll()->toSql())->toBe('select * from users');
});

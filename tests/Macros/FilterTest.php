<?php

use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Illuminate\Database\Eloquent\RelationNotFoundException;

test('query with one column', function () {
    expect(User::filter(['name' => 'foo'])->toSql())
        ->toBe('select * from "users" where ("users"."name" LIKE ?)');
});

test('query with more columns', function () {
    expect(User::filter(['name' => 'foo', 'email' => 'foo'])->toSql())
        ->toBe('select * from "users" where ("users"."name" LIKE ?) and ("users"."email" LIKE ?)');
});

test('query with column nullable', function () {
    expect(User::filter(['name' => 'foo', 'email' => null])->toSql())
        ->toBe('select * from "users" where ("users"."name" LIKE ?)');
});

test('query with allowed columns', function () {
    expect(User::filter(['name' => 'foo', 'page' => '2', 'password' => 'x'], ['name', 'email'])->toSql())
        ->toBe('select * from "users" where ("users"."name" LIKE ?)');
});

test('query ignores array values', function () {
    expect(User::filter(['name' => 'foo', 'email' => ['foo']])->toSql())
        ->toBe('select * from "users" where ("users"."name" LIKE ?)');
});

test('query ignores numeric keys', function () {
    expect(User::filter('foo')->toSql())->toBe('select * from "users"')
        ->and(User::filter(['foo'])->toSql())->toBe('select * from "users"');
});

test('query with escape', function () {
    $query = User::filter(['name' => '10%'], null, true);

    expect($query->toSql())->toBe('select * from "users" where ("users"."name" LIKE ? ESCAPE \'!\')')
        ->and($query->getBindings())->toBe(['%10!%%']);
});

test('query with model method key does not call it', function () {
    User::create(['name' => 'Foo', 'email' => 'foo@bar.com']);

    expect(fn () => User::filter(['truncate.name' => 'foo'])->get())
        ->toThrow(fn (RelationNotFoundException $e) => expect($e->relation)->toBe('truncate'));

    expect(User::count())->toBe(1);
});

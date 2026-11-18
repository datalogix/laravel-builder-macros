<?php

use Datalogix\BuilderMacros\Tests\Database\Models\User;

test('result', function () {
    User::create(['name' => 'name 1', 'email' => 'email1@email1.com']);
    User::create(['name' => 'name 2', 'email' => 'email2@email2.com']);
    User::create(['name' => 'name 3', 'email' => 'email3@email3.com']);

    expect(User::query()->map(fn ($user) => $user->email))->toEqual(collect([
        'email1@email1.com',
        'email2@email2.com',
        'email3@email3.com',
    ]));
});

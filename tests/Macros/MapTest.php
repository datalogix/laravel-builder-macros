<?php

namespace Datalogix\BuilderMacros\Tests\Macros;

use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Datalogix\BuilderMacros\Tests\TestCase;

class MapTest extends TestCase
{
    public function testResult()
    {
        User::create(['name' => 'name 1', 'email' => 'email1@email1.com']);
        User::create(['name' => 'name 2', 'email' => 'email2@email2.com']);
        User::create(['name' => 'name 3', 'email' => 'email3@email3.com']);

        $expected = collect([
            'email1@email1.com',
            'email2@email2.com',
            'email3@email3.com',
        ]);

        $actual = User::query()->map(function ($user) {
            return $user->email;
        });

        $this->assertEquals($expected, $actual);
    }
}

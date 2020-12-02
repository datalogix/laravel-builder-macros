<?php

namespace Datalogix\BuilderMacros\Tests\Macros;

use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Datalogix\BuilderMacros\Tests\TestCase;

class AddSubSelectTest extends TestCase
{
    public function testQuery()
    {
        $expected = 'select "users".*, (select * from "users" limit 1) as "name" from "users"';
        $actual = User::addSubSelect('name', User::query())->toSql();

        $this->assertEquals($expected, $actual);
    }
}

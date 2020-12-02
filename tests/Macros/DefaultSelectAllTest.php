<?php

namespace Datalogix\BuilderMacros\Tests\Macros;

use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Datalogix\BuilderMacros\Tests\TestCase;

class DefaultSelectAllTest extends TestCase
{
    public function testQueryWithoutColumns()
    {
        $expected = 'select "users".* from "users"';
        $actual = User::defaultSelectAll()->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testQueryWithOneColumn()
    {
        $expected = 'select "email" from "users"';
        $actual = User::select('email')->defaultSelectAll()->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testQueryWithMoreColumns()
    {
        $expected = 'select "email", "name" from "users"';
        $actual = User::select('email', 'name')->defaultSelectAll()->toSql();

        $this->assertEquals($expected, $actual);
    }
}

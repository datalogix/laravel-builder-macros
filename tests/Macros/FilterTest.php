<?php

namespace Datalogix\BuilderMacros\Tests\Macros;

use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Datalogix\BuilderMacros\Tests\TestCase;

class FilterTest extends TestCase
{
    public function testQueryWithOneColumn()
    {
        $expected = 'select * from "users" where ("users"."name" LIKE ?)';
        $actual = User::filter(['name' => 'foo'])->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testQueryWithMoreColumns()
    {
        $expected = 'select * from "users" where ("users"."name" LIKE ?) and ("users"."email" LIKE ?)';
        $actual = User::filter(['name' => 'foo', 'email' => 'foo'])->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testQueryWithColumnNullable()
    {
        $expected = 'select * from "users" where ("users"."name" LIKE ?)';
        $actual = User::filter(['name' => 'foo', 'email' => null])->toSql();

        $this->assertEquals($expected, $actual);
    }
}

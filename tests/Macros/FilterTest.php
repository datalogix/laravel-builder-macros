<?php

namespace Datalogix\BuilderMacros\Tests\Macros;

use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Datalogix\BuilderMacros\Tests\TestCase;

class FilterTest extends TestCase
{
    public function test_query_with_one_column()
    {
        $expected = 'select * from "users" where ("users"."name" LIKE ?)';
        $actual = User::filter(['name' => 'foo'])->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_more_columns()
    {
        $expected = 'select * from "users" where ("users"."name" LIKE ?) and ("users"."email" LIKE ?)';
        $actual = User::filter(['name' => 'foo', 'email' => 'foo'])->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_column_nullable()
    {
        $expected = 'select * from "users" where ("users"."name" LIKE ?)';
        $actual = User::filter(['name' => 'foo', 'email' => null])->toSql();

        $this->assertEquals($expected, $actual);
    }
}

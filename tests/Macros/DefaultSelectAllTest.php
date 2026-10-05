<?php

namespace Datalogix\BuilderMacros\Tests\Macros;

use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Datalogix\BuilderMacros\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class DefaultSelectAllTest extends TestCase
{
    public function test_query_without_columns()
    {
        $expected = 'select "users".* from "users"';
        $actual = User::defaultSelectAll()->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_one_column()
    {
        $expected = 'select "email" from "users"';
        $actual = User::select('email')->defaultSelectAll()->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_more_columns()
    {
        $expected = 'select "email", "name" from "users"';
        $actual = User::select('email', 'name')->defaultSelectAll()->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_table_alias()
    {
        $expected = 'select "u".* from "users" as "u"';
        $actual = User::from('users as u')->defaultSelectAll()->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_expression_table()
    {
        $expected = 'select * from users';
        $actual = User::from(DB::raw('users'))->defaultSelectAll()->toSql();

        $this->assertEquals($expected, $actual);
    }
}

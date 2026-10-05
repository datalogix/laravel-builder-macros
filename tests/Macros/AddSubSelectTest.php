<?php

namespace Datalogix\BuilderMacros\Tests\Macros;

use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Datalogix\BuilderMacros\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class AddSubSelectTest extends TestCase
{
    public function test_query()
    {
        $expected = 'select "users".*, (select * from "users" limit 1) as "name" from "users"';
        $actual = User::addSubSelect('name', User::query())->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_does_not_change_sub_query()
    {
        $subQuery = User::query();
        User::addSubSelect('name', $subQuery)->toSql();

        $this->assertNull($subQuery->getQuery()->limit);
    }

    public function test_query_with_base_query_builder()
    {
        $expected = 'select "users".*, (select * from "users" limit 1) as "name" from "users"';
        $actual = User::addSubSelect('name', DB::table('users'))->toSql();

        $this->assertEquals($expected, $actual);
    }
}

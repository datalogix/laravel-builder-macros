<?php

namespace Datalogix\BuilderMacros\Tests\Macros;

use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Datalogix\BuilderMacros\Tests\TestCase;

class WhereLikeTest extends TestCase
{
    public function testQueryWithOneColumn()
    {
        $expected = 'select * from "users" where ("name" LIKE ?)';
        $actual = User::whereLike('name', 'foo')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testQueryWithMoreColumns()
    {
        $expected = 'select * from "users" where ("name" LIKE ? or "email" LIKE ?)';
        $actual = User::whereLike(['name', 'email'], 'foo')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testQueryWithRelation()
    {
        $expected = 'select * from "users" where ("name" LIKE ? or "email" LIKE ? or exists (select * from "posts" where "users"."id" = "posts"."user_id" and "title" LIKE ?))';
        $actual = User::whereLike(['name', 'email', 'posts.title'], 'foo')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testResult()
    {
        $expected = User::create(['name' => 'name', 'email' => 'foo@bar.com']);
        $actual = User::whereLike(['name', 'email'], 'bar')->first();

        $this->assertEquals($expected->id, $actual->id);
    }

    public function testResultWithRelation()
    {
        $expected = User::create(['name' => 'foo', 'email' => 'foo@bar.com']);
        $expected->posts()->create(['title' => 'baz']);
        $actual = User::whereLike('posts.title', 'baz')->first();

        $this->assertEquals($expected->id, $actual->id);
    }
}

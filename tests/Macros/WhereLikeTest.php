<?php

namespace Datalogix\BuilderMacros\Tests\Macros;

use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Datalogix\BuilderMacros\Tests\TestCase;

class WhereLikeTest extends TestCase
{
    public function testQueryWithBlankValue()
    {
        $this->assertEquals('select * from "users"', User::whereLike('name', null)->toSql());
        $this->assertEquals('select * from "users"', User::whereLike('name', '')->toSql());
    }

    public function testQueryWithFilledValue()
    {
        $this->assertEquals('select * from "users" where ("users"."name" LIKE ?)', User::whereLike('name', false)->toSql());
        $this->assertEquals('select * from "users" where ("users"."name" LIKE ?)', User::whereLike('name', 0)->toSql());
    }

    public function testQueryWithOneColumn()
    {
        $expected = 'select * from "users" where ("users"."name" LIKE ?)';
        $actual = User::whereLike('name', 'foo')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testQueryWithMoreColumns()
    {
        $expected = 'select * from "users" where ("users"."name" LIKE ? or "users"."email" LIKE ?)';
        $actual = User::whereLike(['name', 'email'], 'foo')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testQueryWithRelation()
    {
        $expected = 'select * from "users" where ("users"."name" LIKE ? or "users"."email" LIKE ? or exists (select * from "posts" where "users"."id" = "posts"."user_id" and "title" LIKE ?))';
        $actual = User::whereLike(['name', 'email', 'posts.title'], 'foo')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testQueryWithSubRelation()
    {
        $expected = 'select * from "users" where ("users"."name" LIKE ? or "users"."email" LIKE ? or exists (select * from "posts" where "users"."id" = "posts"."user_id" and exists (select * from "comments" where "posts"."id" = "comments"."post_id" and "body" LIKE ?)))';
        $actual = User::whereLike(['name', 'email', 'posts.comments.body'], 'foo')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testQueryWithColumnKey()
    {
        $expected = 'select * from "users" where ("users"."name_id" = ?)';
        $actual = User::whereLike(['name_id'], 'foo')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function testQueryWithRelationColumnKey()
    {
        $expected = 'select * from "users" where (exists (select * from "posts" where "users"."id" = "posts"."user_id" and "author_id" = ?))';
        $actual = User::whereLike(['posts.author_id'], 'foo')->toSql();

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

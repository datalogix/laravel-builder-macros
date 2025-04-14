<?php

namespace Datalogix\BuilderMacros\Tests\Macros;

use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Datalogix\BuilderMacros\Tests\TestCase;

class WhereLikeTest extends TestCase
{
    public function test_query_with_blank_value()
    {
        $this->assertEquals('select * from "users"', User::whereLike('name', null)->toSql());
        $this->assertEquals('select * from "users"', User::whereLike('name', '')->toSql());
    }

    public function test_query_with_filled_value()
    {
        $this->assertEquals('select * from "users" where ("users"."name" LIKE ?)', User::whereLike('name', false)->toSql());
        $this->assertEquals('select * from "users" where ("users"."name" LIKE ?)', User::whereLike('name', 0)->toSql());
    }

    public function test_query_with_one_column()
    {
        $expected = 'select * from "users" where ("users"."name" LIKE ?)';
        $actual = User::whereLike('name', 'foo')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_more_columns()
    {
        $expected = 'select * from "users" where ("users"."name" LIKE ? or "users"."email" LIKE ?)';
        $actual = User::whereLike(['name', 'email'], 'foo')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_relation()
    {
        $expected = 'select * from "users" where ("users"."name" LIKE ? or "users"."email" LIKE ? or exists (select * from "posts" where "users"."id" = "posts"."user_id" and "title" LIKE ?))';
        $actual = User::whereLike(['name', 'email', 'posts.title'], 'foo')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_sub_relation()
    {
        $expected = 'select * from "users" where ("users"."name" LIKE ? or "users"."email" LIKE ? or exists (select * from "posts" where "users"."id" = "posts"."user_id" and exists (select * from "comments" where "posts"."id" = "comments"."post_id" and "body" LIKE ?)))';
        $actual = User::whereLike(['name', 'email', 'posts.comments.body'], 'foo')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_column_key()
    {
        $expected = 'select * from "users" where ("users"."name_id" = ?)';
        $actual = User::whereLike(['name_id'], 'foo')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_relation_column_key()
    {
        $expected = 'select * from "users" where (exists (select * from "posts" where "users"."id" = "posts"."user_id" and "author_id" = ?))';
        $actual = User::whereLike(['posts.author_id'], 'foo')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_result()
    {
        $expected = User::create(['name' => 'name', 'email' => 'foo@bar.com']);
        $actual = User::whereLike(['name', 'email'], 'bar')->first();

        $this->assertEquals($expected->id, $actual->id);
    }

    public function test_result_with_relation()
    {
        $expected = User::create(['name' => 'foo', 'email' => 'foo@bar.com']);
        $expected->posts()->create(['title' => 'baz']);
        $actual = User::whereLike('posts.title', 'baz')->first();

        $this->assertEquals($expected->id, $actual->id);
    }
}

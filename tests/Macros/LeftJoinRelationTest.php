<?php

namespace Datalogix\BuilderMacros\Tests\Macros;

use Datalogix\BuilderMacros\Tests\Database\Models\Post;
use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Datalogix\BuilderMacros\Tests\TestCase;

class LeftJoinRelationTest extends TestCase
{
    public function test_query()
    {
        $expected = 'select "posts".* from "posts" left join "users" on "posts"."user_id" = "users"."id"';
        $actual = Post::query()->leftJoinRelation('user')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_has_many()
    {
        $expected = 'select "users".* from "users" left join "posts" on "users"."id" = "posts"."user_id"';
        $actual = User::leftJoinRelation('posts')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_self_relation()
    {
        $expected = 'select "users".* from "users" left join "users" as "parent" on "users"."parent_id" = "parent"."id"';
        $actual = User::leftJoinRelation('parent')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_morph_many()
    {
        $expected = 'select "posts".* from "posts" left join "images" on "posts"."id" = "images"."imageable_id" and "images"."imageable_type" = ? and "images"."deleted_at" is null';
        $actual = Post::leftJoinRelation('images')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_nested_relation()
    {
        $expected = 'select "users".* from "users" left join "posts" on "users"."id" = "posts"."user_id" left join "comments" on "posts"."id" = "comments"."post_id"';
        $actual = User::leftJoinRelation('posts.comments')->toSql();

        $this->assertEquals($expected, $actual);
    }
}

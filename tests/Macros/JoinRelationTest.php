<?php

namespace Datalogix\BuilderMacros\Tests\Macros;

use Datalogix\BuilderMacros\Tests\Database\Models\Post;
use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Datalogix\BuilderMacros\Tests\TestCase;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class JoinRelationTest extends TestCase
{
    public function test_query()
    {
        $expected = 'select "posts".* from "posts" inner join "users" on "posts"."user_id" = "users"."id"';
        $actual = Post::joinRelation('user')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_has_many()
    {
        $expected = 'select "users".* from "users" inner join "posts" on "users"."id" = "posts"."user_id"';
        $actual = User::joinRelation('posts')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_unsupported_relation()
    {
        $this->expectException(InvalidArgumentException::class);

        User::joinRelation('latestComment');
    }

    public function test_query_with_table_alias()
    {
        $expected = 'select "p".* from "posts" as "p" inner join "users" on "p"."user_id" = "users"."id"';
        $actual = Post::from('posts as p')->joinRelation('user')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_selected_columns()
    {
        $expected = 'select "posts"."title", "users"."name" from "posts" inner join "users" on "posts"."user_id" = "users"."id"';
        $actual = Post::select('posts.title', 'users.name')->joinRelation('user')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_result_keeps_model_attributes()
    {
        User::create(['name' => 'foo', 'email' => 'foo@bar.com']);
        $user = User::create(['name' => 'bar', 'email' => 'bar@bar.com']);
        $post = $user->posts()->create(['title' => 'baz']);

        $this->assertEquals($post->id, Post::joinRelation('user')->first()->id);
    }

    public function test_query_with_expression_table()
    {
        $expected = 'select * from posts inner join "users" on "posts"."user_id" = "users"."id"';
        $actual = Post::from(DB::raw('posts'))->joinRelation('user')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_self_belongs_to()
    {
        $expected = 'select "users".* from "users" inner join "users" as "parent" on "users"."parent_id" = "parent"."id"';
        $actual = User::joinRelation('parent')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_self_has_many()
    {
        $expected = 'select "users".* from "users" inner join "users" as "children" on "users"."id" = "children"."parent_id"';
        $actual = User::joinRelation('children')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_self_relation_and_table_alias()
    {
        $expected = 'select "u".* from "users" as "u" inner join "users" on "u"."parent_id" = "users"."id"';
        $actual = User::from('users as u')->joinRelation('parent')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_result_with_self_relation()
    {
        $parent = User::create(['name' => 'parent', 'email' => 'parent@bar.com']);
        $child = User::create(['name' => 'child', 'email' => 'child@bar.com', 'parent_id' => $parent->id]);

        $actual = User::joinRelation('parent')->addSelect('parent.name as parent_name')->get();

        $this->assertCount(1, $actual);
        $this->assertEquals($child->id, $actual->first()->id);
        $this->assertEquals('parent', $actual->first()->parent_name);
    }
}

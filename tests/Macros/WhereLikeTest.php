<?php

namespace Datalogix\BuilderMacros\Tests\Macros;

use Datalogix\BuilderMacros\Tests\Database\Models\Post;
use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Datalogix\BuilderMacros\Tests\TestCase;
use Illuminate\Support\Facades\DB;

class WhereLikeTest extends TestCase
{
    public function test_query_with_blank_value()
    {
        $this->assertEquals('select * from "users"', User::whereLike('name', null)->toSql());
        $this->assertEquals('select * from "users"', User::whereLike('name', '')->toSql());
    }

    public function test_query_with_boolean_value()
    {
        $this->assertEquals('select * from "users"', User::whereLike('name', false)->toSql());
        $this->assertEquals('select * from "users"', User::whereLike('name', true)->toSql());
    }

    public function test_query_with_filled_value()
    {
        $this->assertEquals('select * from "users" where ("users"."name" LIKE ?)', User::whereLike('name', 0)->toSql());
        $this->assertEquals('select * from "users" where ("users"."name" LIKE ?)', User::whereLike('name', '0')->toSql());
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

    public function test_query_with_table_alias()
    {
        $expected = 'select * from "users" as "u" where ("u"."name" LIKE ?)';
        $actual = User::from('users as u')->whereLike('name', 'foo')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_bindings_with_start_and_end()
    {
        $this->assertEquals(['%foo%'], User::whereLike('name', 'foo')->getBindings());
        $this->assertEquals(['foo%'], User::whereLike('name', 'foo', false)->getBindings());
        $this->assertEquals(['%foo'], User::whereLike('name', 'foo', true, false)->getBindings());
    }

    public function test_query_with_expression_table()
    {
        $expected = 'select * from users where ("name" LIKE ?)';
        $actual = User::from(DB::raw('users'))->whereLike('name', 'foo')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_escape()
    {
        $expected = 'select * from "users" where ("users"."name" LIKE ? ESCAPE \'!\' or exists (select * from "posts" where "users"."id" = "posts"."user_id" and "title" LIKE ? ESCAPE \'!\'))';
        $query = User::whereLike(['name', 'posts.title'], '10%_!', true, true, true);

        $this->assertEquals($expected, $query->toSql());
        $this->assertEquals(['%10!%!_!!%', '%10!%!_!!%'], $query->getBindings());
    }

    public function test_query_with_escape_named_argument()
    {
        $this->assertEquals(['10!%%'], User::whereLike('name', '10%', start: false, escape: true)->getBindings());
    }

    public function test_result_with_escape()
    {
        $user = User::create(['name' => 'foo', 'email' => 'foo@bar.com']);
        $user->posts()->create(['title' => '100% off']);
        $user->posts()->create(['title' => '1000 off']);
        $user->posts()->create(['title' => 'a_b']);
        $user->posts()->create(['title' => 'axb']);
        $user->posts()->create(['title' => 'wow!']);

        $this->assertEquals(['100% off'], Post::whereLike('title', '100%', true, true, true)->pluck('title')->all());
        $this->assertEquals(['a_b'], Post::whereLike('title', 'a_b', true, true, true)->pluck('title')->all());
        $this->assertEquals(['wow!'], Post::whereLike('title', '!', true, true, true)->pluck('title')->all());
        $this->assertCount(2, Post::whereLike('title', '100%')->get());
    }

    public function test_query_with_escape_on_sql_server()
    {
        // The query is only compiled, so no connection is made
        config(['database.connections.sqlsrv_test' => ['driver' => 'sqlsrv', 'database' => 'test']]);

        $query = User::on('sqlsrv_test')->whereLike('name', '[a]%', true, true, true);

        $this->assertEquals('select * from [users] where ([users].[name] LIKE ? ESCAPE \'!\')', $query->toSql());
        $this->assertEquals(['%![a]!%%'], $query->getBindings());
    }
}

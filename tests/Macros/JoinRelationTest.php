<?php

namespace Datalogix\BuilderMacros\Tests\Macros;

use Datalogix\BuilderMacros\Tests\Database\Models\Comment;
use Datalogix\BuilderMacros\Tests\Database\Models\Image;
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

    public function test_query_with_morph_many()
    {
        $expected = 'select "posts".* from "posts" inner join "images" on "posts"."id" = "images"."imageable_id" and "images"."imageable_type" = ? and "images"."deleted_at" is null';
        $query = Post::joinRelation('images');

        $this->assertEquals($expected, $query->toSql());
        $this->assertEquals([Post::class], $query->getBindings());
    }

    public function test_query_with_morph_one()
    {
        $expected = 'select "users".* from "users" inner join "images" on "users"."id" = "images"."imageable_id" and "images"."imageable_type" = ? and "images"."deleted_at" is null';
        $query = User::joinRelation('avatar');

        $this->assertEquals($expected, $query->toSql());
        $this->assertEquals([User::class], $query->getBindings());
    }

    public function test_result_with_morph_relation()
    {
        $user = User::create(['name' => 'foo', 'email' => 'foo@bar.com']);
        $post = $user->posts()->create(['title' => 'baz']);

        // Same id, different types
        $this->assertEquals($user->id, $post->id);

        $user->avatar()->create(['url' => 'avatar.png']);
        $post->images()->create(['url' => 'post.png']);

        $actual = Post::joinRelation('images')->addSelect('images.url')->get();

        $this->assertCount(1, $actual);
        $this->assertEquals('post.png', $actual->first()->url);
    }

    public function test_query_with_morph_to()
    {
        $this->expectException(InvalidArgumentException::class);

        Image::joinRelation('imageable');
    }

    public function test_query_with_relations_to_the_same_table()
    {
        $expected = 'select "posts".* from "posts" inner join "users" on "posts"."user_id" = "users"."id" inner join "users" as "editor" on "posts"."editor_id" = "editor"."id"';
        $actual = Post::joinRelation('user')->joinRelation('editor')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_result_with_relations_to_the_same_table()
    {
        $author = User::create(['name' => 'author', 'email' => 'author@bar.com']);
        $editor = User::create(['name' => 'editor', 'email' => 'editor@bar.com']);
        $author->posts()->create(['title' => 'baz', 'editor_id' => $editor->id]);

        $actual = Post::joinRelation('user')
            ->joinRelation('editor')
            ->addSelect('users.name as author_name', 'editor.name as editor_name')
            ->first();

        $this->assertEquals('author', $actual->author_name);
        $this->assertEquals('editor', $actual->editor_name);
    }

    public function test_result_without_soft_deleted_rows()
    {
        $user = User::create(['name' => 'foo', 'email' => 'foo@bar.com']);
        $post = $user->posts()->create(['title' => 'baz']);
        $post->images()->create(['url' => 'kept.png']);
        $post->images()->create(['url' => 'deleted.png'])->delete();

        $actual = Post::joinRelation('images')->addSelect('images.url')->get();

        $this->assertEquals(['kept.png'], $actual->pluck('url')->all());
    }

    public function test_query_with_nested_relation()
    {
        $expected = 'select "users".* from "users" inner join "posts" on "users"."id" = "posts"."user_id" inner join "comments" on "posts"."id" = "comments"."post_id"';
        $actual = User::joinRelation('posts.comments')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_nested_relation_to_the_same_table()
    {
        $expected = 'select "posts".* from "posts" inner join "users" on "posts"."user_id" = "users"."id" inner join "users" as "parent" on "users"."parent_id" = "parent"."id"';
        $actual = Post::joinRelation('user.parent')->toSql();

        $this->assertEquals($expected, $actual);
    }

    public function test_query_with_nested_unsupported_relation()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Relation [latestComment]');

        Post::joinRelation('user.latestComment');
    }

    public function test_result_with_nested_relation()
    {
        $parent = User::create(['name' => 'parent', 'email' => 'parent@bar.com']);
        $user = User::create(['name' => 'foo', 'email' => 'foo@bar.com', 'parent_id' => $parent->id]);
        $post = $user->posts()->create(['title' => 'baz']);
        $post->comments()->create(['body' => 'qux']);

        $actual = Comment::joinRelation('post.user.parent')->addSelect('parent.name as parent_name')->first();

        $this->assertEquals('parent', $actual->parent_name);
    }
}

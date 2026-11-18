<?php

use Datalogix\BuilderMacros\Tests\Database\Models\Comment;
use Datalogix\BuilderMacros\Tests\Database\Models\Image;
use Datalogix\BuilderMacros\Tests\Database\Models\Post;
use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Illuminate\Support\Facades\DB;

test('query', function () {
    expect(Post::joinRelation('user')->toSql())
        ->toBe('select "posts".* from "posts" inner join "users" on "posts"."user_id" = "users"."id"');
});

test('query with has many', function () {
    expect(User::joinRelation('posts')->toSql())
        ->toBe('select "users".* from "users" inner join "posts" on "users"."id" = "posts"."user_id"');
});

test('query with unsupported relation', function () {
    User::joinRelation('latestComment');
})->throws(InvalidArgumentException::class);

test('query with table alias', function () {
    expect(Post::from('posts as p')->joinRelation('user')->toSql())
        ->toBe('select "p".* from "posts" as "p" inner join "users" on "p"."user_id" = "users"."id"');
});

test('query with selected columns', function () {
    expect(Post::select('posts.title', 'users.name')->joinRelation('user')->toSql())
        ->toBe('select "posts"."title", "users"."name" from "posts" inner join "users" on "posts"."user_id" = "users"."id"');
});

test('result keeps model attributes', function () {
    User::create(['name' => 'foo', 'email' => 'foo@bar.com']);
    $user = User::create(['name' => 'bar', 'email' => 'bar@bar.com']);
    $post = $user->posts()->create(['title' => 'baz']);

    expect(Post::joinRelation('user')->first()->id)->toBe($post->id);
});

test('query with expression table', function () {
    expect(Post::from(DB::raw('posts'))->joinRelation('user')->toSql())
        ->toBe('select * from posts inner join "users" on "posts"."user_id" = "users"."id"');
});

test('query with self belongs to', function () {
    expect(User::joinRelation('parent')->toSql())
        ->toBe('select "users".* from "users" inner join "users" as "parent" on "users"."parent_id" = "parent"."id"');
});

test('query with self has many', function () {
    expect(User::joinRelation('children')->toSql())
        ->toBe('select "users".* from "users" inner join "users" as "children" on "users"."id" = "children"."parent_id"');
});

test('query with self relation and table alias', function () {
    expect(User::from('users as u')->joinRelation('parent')->toSql())
        ->toBe('select "u".* from "users" as "u" inner join "users" on "u"."parent_id" = "users"."id"');
});

test('result with self relation', function () {
    $parent = User::create(['name' => 'parent', 'email' => 'parent@bar.com']);
    $child = User::create(['name' => 'child', 'email' => 'child@bar.com', 'parent_id' => $parent->id]);

    $actual = User::joinRelation('parent')->addSelect('parent.name as parent_name')->get();

    expect($actual)->toHaveCount(1)
        ->and($actual->first()->id)->toBe($child->id)
        ->and($actual->first()->parent_name)->toBe('parent');
});

test('query with morph many', function () {
    $query = Post::joinRelation('images');

    expect($query->toSql())->toBe('select "posts".* from "posts" inner join "images" on "posts"."id" = "images"."imageable_id" and "images"."imageable_type" = ? and "images"."deleted_at" is null')
        ->and($query->getBindings())->toBe([Post::class]);
});

test('query with morph one', function () {
    $query = User::joinRelation('avatar');

    expect($query->toSql())->toBe('select "users".* from "users" inner join "images" on "users"."id" = "images"."imageable_id" and "images"."imageable_type" = ? and "images"."deleted_at" is null')
        ->and($query->getBindings())->toBe([User::class]);
});

test('result with morph relation', function () {
    $user = User::create(['name' => 'foo', 'email' => 'foo@bar.com']);
    $post = $user->posts()->create(['title' => 'baz']);

    // Same id, different types
    expect($post->id)->toBe($user->id);

    $user->avatar()->create(['url' => 'avatar.png']);
    $post->images()->create(['url' => 'post.png']);

    $actual = Post::joinRelation('images')->addSelect('images.url')->get();

    expect($actual)->toHaveCount(1)
        ->and($actual->first()->url)->toBe('post.png');
});

test('query with morph to', function () {
    Image::joinRelation('imageable');
})->throws(InvalidArgumentException::class);

test('query with relations to the same table', function () {
    expect(Post::joinRelation('user')->joinRelation('editor')->toSql())
        ->toBe('select "posts".* from "posts" inner join "users" on "posts"."user_id" = "users"."id" inner join "users" as "editor" on "posts"."editor_id" = "editor"."id"');
});

test('result with relations to the same table', function () {
    $author = User::create(['name' => 'author', 'email' => 'author@bar.com']);
    $editor = User::create(['name' => 'editor', 'email' => 'editor@bar.com']);
    $author->posts()->create(['title' => 'baz', 'editor_id' => $editor->id]);

    $actual = Post::joinRelation('user')
        ->joinRelation('editor')
        ->addSelect('users.name as author_name', 'editor.name as editor_name')
        ->first();

    expect($actual->author_name)->toBe('author')
        ->and($actual->editor_name)->toBe('editor');
});

test('result without soft deleted rows', function () {
    $user = User::create(['name' => 'foo', 'email' => 'foo@bar.com']);
    $post = $user->posts()->create(['title' => 'baz']);
    $post->images()->create(['url' => 'kept.png']);
    $post->images()->create(['url' => 'deleted.png'])->delete();

    $actual = Post::joinRelation('images')->addSelect('images.url')->get();

    expect($actual->pluck('url')->all())->toBe(['kept.png']);
});

test('query with nested relation', function () {
    expect(User::joinRelation('posts.comments')->toSql())
        ->toBe('select "users".* from "users" inner join "posts" on "users"."id" = "posts"."user_id" inner join "comments" on "posts"."id" = "comments"."post_id"');
});

test('query with nested relation to the same table', function () {
    expect(Post::joinRelation('user.parent')->toSql())
        ->toBe('select "posts".* from "posts" inner join "users" on "posts"."user_id" = "users"."id" inner join "users" as "parent" on "users"."parent_id" = "parent"."id"');
});

test('query with nested unsupported relation', function () {
    Post::joinRelation('user.latestComment');
})->throws(InvalidArgumentException::class, 'Relation [latestComment]');

test('result with nested relation', function () {
    $parent = User::create(['name' => 'parent', 'email' => 'parent@bar.com']);
    $user = User::create(['name' => 'foo', 'email' => 'foo@bar.com', 'parent_id' => $parent->id]);
    $post = $user->posts()->create(['title' => 'baz']);
    $post->comments()->create(['body' => 'qux']);

    $actual = Comment::joinRelation('post.user.parent')->addSelect('parent.name as parent_name')->first();

    expect($actual->parent_name)->toBe('parent');
});

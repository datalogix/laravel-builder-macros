<?php

use Datalogix\BuilderMacros\Tests\Database\Models\Post;
use Datalogix\BuilderMacros\Tests\Database\Models\User;

test('query', function () {
    expect(Post::query()->leftJoinRelation('user')->toSql())
        ->toBe('select "posts".* from "posts" left join "users" on "posts"."user_id" = "users"."id"');
});

test('query with has many', function () {
    expect(User::leftJoinRelation('posts')->toSql())
        ->toBe('select "users".* from "users" left join "posts" on "users"."id" = "posts"."user_id"');
});

test('query with self relation', function () {
    expect(User::leftJoinRelation('parent')->toSql())
        ->toBe('select "users".* from "users" left join "users" as "parent" on "users"."parent_id" = "parent"."id"');
});

test('query with morph many', function () {
    expect(Post::leftJoinRelation('images')->toSql())
        ->toBe('select "posts".* from "posts" left join "images" on "posts"."id" = "images"."imageable_id" and "images"."imageable_type" = ? and "images"."deleted_at" is null');
});

test('query with nested relation', function () {
    expect(User::leftJoinRelation('posts.comments')->toSql())
        ->toBe('select "users".* from "users" left join "posts" on "users"."id" = "posts"."user_id" left join "comments" on "posts"."id" = "comments"."post_id"');
});

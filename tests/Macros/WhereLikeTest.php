<?php

use Datalogix\BuilderMacros\Tests\Database\Models\Image;
use Datalogix\BuilderMacros\Tests\Database\Models\Post;
use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Support\Facades\DB;

test('query with blank value', function () {
    expect(User::whereLike('name', null)->toSql())->toBe('select * from "users"')
        ->and(User::whereLike('name', '')->toSql())->toBe('select * from "users"');
});

test('query with boolean value', function () {
    expect(User::whereLike('name', false)->toSql())->toBe('select * from "users"')
        ->and(User::whereLike('name', true)->toSql())->toBe('select * from "users"');
});

test('query with filled value', function () {
    expect(User::whereLike('name', 0)->toSql())->toBe('select * from "users" where ("users"."name" LIKE ?)')
        ->and(User::whereLike('name', '0')->toSql())->toBe('select * from "users" where ("users"."name" LIKE ?)');
});

test('query with one column', function () {
    expect(User::whereLike('name', 'foo')->toSql())
        ->toBe('select * from "users" where ("users"."name" LIKE ?)');
});

test('query with more columns', function () {
    expect(User::whereLike(['name', 'email'], 'foo')->toSql())
        ->toBe('select * from "users" where ("users"."name" LIKE ? or "users"."email" LIKE ?)');
});

test('query with relation', function () {
    expect(User::whereLike(['name', 'email', 'posts.title'], 'foo')->toSql())
        ->toBe('select * from "users" where ("users"."name" LIKE ? or "users"."email" LIKE ? or exists (select * from "posts" where "users"."id" = "posts"."user_id" and "title" LIKE ?))');
});

test('query with sub relation', function () {
    expect(User::whereLike(['name', 'email', 'posts.comments.body'], 'foo')->toSql())
        ->toBe('select * from "users" where ("users"."name" LIKE ? or "users"."email" LIKE ? or exists (select * from "posts" where "users"."id" = "posts"."user_id" and exists (select * from "comments" where "posts"."id" = "comments"."post_id" and "body" LIKE ?)))');
});

test('query with column key', function () {
    expect(User::whereLike(['name_id'], 'foo')->toSql())
        ->toBe('select * from "users" where ("users"."name_id" = ?)');
});

test('query with relation column key', function () {
    expect(User::whereLike(['posts.author_id'], 'foo')->toSql())
        ->toBe('select * from "users" where (exists (select * from "posts" where "users"."id" = "posts"."user_id" and "author_id" = ?))');
});

test('result', function () {
    $expected = User::create(['name' => 'name', 'email' => 'foo@bar.com']);

    expect(User::whereLike(['name', 'email'], 'bar')->first()->id)->toBe($expected->id);
});

test('result with relation', function () {
    $expected = User::create(['name' => 'foo', 'email' => 'foo@bar.com']);
    $expected->posts()->create(['title' => 'baz']);

    expect(User::whereLike('posts.title', 'baz')->first()->id)->toBe($expected->id);
});

test('query with table alias', function () {
    expect(User::from('users as u')->whereLike('name', 'foo')->toSql())
        ->toBe('select * from "users" as "u" where ("u"."name" LIKE ?)');
});

test('query bindings with start and end', function () {
    expect(User::whereLike('name', 'foo')->getBindings())->toBe(['%foo%'])
        ->and(User::whereLike('name', 'foo', false)->getBindings())->toBe(['foo%'])
        ->and(User::whereLike('name', 'foo', true, false)->getBindings())->toBe(['%foo']);
});

test('query with expression table', function () {
    expect(User::from(DB::raw('users'))->whereLike('name', 'foo')->toSql())
        ->toBe('select * from users where ("name" LIKE ?)');
});

test('query with escape', function () {
    $query = User::whereLike(['name', 'posts.title'], '10%_!', true, true, true);

    expect($query->toSql())->toBe('select * from "users" where ("users"."name" LIKE ? ESCAPE \'!\' or exists (select * from "posts" where "users"."id" = "posts"."user_id" and "title" LIKE ? ESCAPE \'!\'))')
        ->and($query->getBindings())->toBe(['%10!%!_!!%', '%10!%!_!!%']);
});

test('query with escape named argument', function () {
    expect(User::whereLike('name', '10%', start: false, escape: true)->getBindings())->toBe(['10!%%']);
});

test('result with escape', function () {
    $user = User::create(['name' => 'foo', 'email' => 'foo@bar.com']);
    $user->posts()->create(['title' => '100% off']);
    $user->posts()->create(['title' => '1000 off']);
    $user->posts()->create(['title' => 'a_b']);
    $user->posts()->create(['title' => 'axb']);
    $user->posts()->create(['title' => 'wow!']);

    expect(Post::whereLike('title', '100%', true, true, true)->pluck('title')->all())->toBe(['100% off'])
        ->and(Post::whereLike('title', 'a_b', true, true, true)->pluck('title')->all())->toBe(['a_b'])
        ->and(Post::whereLike('title', '!', true, true, true)->pluck('title')->all())->toBe(['wow!'])
        ->and(Post::whereLike('title', '100%')->get())->toHaveCount(2);
});

test('query with escape on sql server', function () {
    // The query is only compiled, so no connection is made
    config(['database.connections.sqlsrv_test' => ['driver' => 'sqlsrv', 'database' => 'test']]);

    $query = User::on('sqlsrv_test')->whereLike('name', '[a]%', true, true, true);

    expect($query->toSql())->toBe('select * from [users] where ([users].[name] LIKE ? ESCAPE \'!\')')
        ->and($query->getBindings())->toBe(['%![a]!%%']);
});

test('query with ignore case on postgres', function () {
    // The query is only compiled, so no connection is made
    config(['database.connections.pgsql_test' => ['driver' => 'pgsql', 'database' => 'test']]);

    expect(User::on('pgsql_test')->whereLike(['name', 'posts.title'], 'foo', ignoreCase: true)->toSql())
        ->toBe('select * from "users" where ("users"."name"::text ILIKE ? or exists (select * from "posts" where "users"."id" = "posts"."user_id" and "title"::text ILIKE ?))')
        ->and(User::on('pgsql_test')->whereLike('name', '10%', true, true, true, true)->toSql())
        ->toBe('select * from "users" where ("users"."name" ILIKE ? ESCAPE \'!\')');
});

test('query without ignore case on postgres', function () {
    config(['database.connections.pgsql_test' => ['driver' => 'pgsql', 'database' => 'test']]);

    expect(User::on('pgsql_test')->whereLike('name', 'foo')->toSql())
        ->toBe('select * from "users" where ("users"."name"::text LIKE ?)');
});

test('query with ignore case on other databases', function () {
    config(['database.connections.sqlsrv_test' => ['driver' => 'sqlsrv', 'database' => 'test']]);

    expect(User::whereLike('name', 'foo', ignoreCase: true)->toSql())
        ->toBe('select * from "users" where ("users"."name" LIKE ?)')
        ->and(User::on('sqlsrv_test')->whereLike('name', 'foo', escape: true, ignoreCase: true)->toSql())
        ->toBe('select * from [users] where ([users].[name] LIKE ? ESCAPE \'!\')');
});

test('result with ignore case', function () {
    User::create(['name' => 'Foo', 'email' => 'foo@bar.com']);

    expect(User::whereLike('name', 'foo', ignoreCase: true)->pluck('name')->all())->toBe(['Foo'])
        ->and(User::whereLike('name', 'foo', escape: true, ignoreCase: true)->pluck('name')->all())->toBe(['Foo']);
});

test('query with model method as relation does not call it', function () {
    User::create(['name' => 'Foo', 'email' => 'foo@bar.com']);

    foreach (['truncate', 'save', 'delete', 'touch', 'hasOne', 'query', 'getTable'] as $method) {
        expect(fn () => User::whereLike($method.'.name', 'foo')->get())
            ->toThrow(fn (RelationNotFoundException $e) => expect($e->relation)->toBe($method));
    }

    expect(User::count())->toBe(1);
});

test('query with model method as sub relation does not call it', function () {
    try {
        Post::whereLike('images.restore.url', 'foo')->get();
    } finally {
        expect(Image::withTrashed()->count())->toBe(0);
    }
})->throws(RelationNotFoundException::class);

test('query with relation requiring arguments', function () {
    User::whereLike('postsWithTitle.title', 'foo')->toSql();
})->throws(RelationNotFoundException::class);

test('query with model method not returning relation', function () {
    User::whereLike('initials.x', 'foo')->toSql();
})->throws(RelationNotFoundException::class);

test('query with laravel trait method as relation does not call it', function () {
    foreach (['markEmailAsVerified', 'sendEmailVerificationNotification'] as $method) {
        expect(fn () => User::whereLike($method.'.name', 'foo')->get())
            ->toThrow(fn (RelationNotFoundException $e) => expect($e->relation)->toBe($method));
    }

    expect(User::count())->toBe(0);
});

test('query with overridden model method as relation does not call it', function () {
    foreach (['save', 'SAVE'] as $method) {
        expect(fn () => Post::whereLike($method.'.title', 'foo')->get())
            ->toThrow(fn (RelationNotFoundException $e) => expect($e->relation)->toBe($method));
    }

    expect(Post::count())->toBe(0);
});

test('query with relation after morph to', function () {
    Post::whereLike('images.imageable.user.name', 'foo')->toSql();
})->throws(InvalidArgumentException::class, 'Relation [imageable] is a MorphTo relation');

test('query with morph to as last relation', function () {
    expect(Image::whereLike('imageable.name', 'foo')->toSql())->toContain('"images"');
});

test('query with dynamic relation', function () {
    User::resolveRelationUsing('articles', fn (User $user) => $user->hasMany(Post::class));

    expect(User::whereLike('articles.title', 'foo')->toSql())
        ->toBe('select * from "users" where (exists (select * from "posts" where "users"."id" = "posts"."user_id" and "title" LIKE ?))');
});

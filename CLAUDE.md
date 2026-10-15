# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

`datalogix/laravel-builder-macros`: a Laravel package (PHP ^8.2, Laravel 12/13) that adds macros to the Eloquent `Builder`. There is no application, only the library in `src/` and its tests. `README.md` is the user-facing documentation of each macro; keep it in sync when a macro's behavior or signature changes.

## Commands

```bash
composer install
composer test                                   # vendor/bin/phpunit (config: phpunit.xml.dist, writes coverage)
vendor/bin/phpunit --no-coverage                # faster, no coverage driver needed
vendor/bin/phpunit --filter test_name           # single test
vendor/bin/phpunit tests/Macros/WhereLikeTest.php
composer lint                                   # vendor/bin/pint (Laravel preset)
```

CI runs the shared workflow `datalogix/workflows/.github/workflows/laravel-tests.yml@v1`.

## Architecture

- `BuilderMacroServiceProvider` (auto-discovered via `extra.laravel.providers`) maps macro names to classes and registers each one with `Builder::macro($name, app($class)())`. A macro already registered globally under the same name is skipped, so apps can override them.
- Each macro is an invokable class in `src/Macros/` whose `__invoke()` returns a closure. The closure is bound to the Eloquent `Builder` at call time, so `$this` inside it is the builder (hence the `@mixin Builder` docblocks). Macros return `$this` to stay chainable, except `map`, which returns a Collection.
- Macros compose each other through `$this`: `filter` calls `whereLike`; `joinRelation` calls `defaultSelectAll`. `search` and `whereLike` are the same class registered under two names; `search` exists because the query builder has a native `whereLike` (the Eloquent macro shadows it with a different signature).
- `LeftJoinRelation` only overrides the `$type` property of `JoinRelation`; the join type is captured before the closure is returned.
- `Support\Table::reference()` resolves the name used to reference a table (`users as u` → `u`, non-string `from` → `null`). Use it whenever building qualified column names from `$query->from` or join tables.

Key behaviors that tests cover and are easy to break:
- `whereLike`: columns with `.` become `orWhereHas` on the relation path; columns ending in `_id` use `=`; blank and boolean values are no-ops; `$escape` uses `LIKE ? ESCAPE '!'` (plus `[` on `sqlsrv`) so it is portable across drivers.
- `joinRelation`: supports BelongsTo (not MorphTo), HasOne/HasMany and MorphOne/MorphMany, with nested paths (`posts.comments`). It adds the morph type and `SoftDeletes` constraints to the join, and aliases the table as the relation name when it is the main table or already joined.

## Tests

- `tests/TestCase.php` extends `Orchestra\Testbench\TestCase`, registers the provider via `getPackageProviders()` and uses `RefreshDatabase` with migrations from `tests/Database/Migrations` on SQLite in memory (`DB_CONNECTION=testing` in `phpunit.xml.dist`).
- Fixture models (`User`, `Post`, `Comment`, `Image`) live in `tests/Database/Models`; add relations/columns there and in the migrations when a test needs them.
- One test class per macro in `tests/Macros/`, methods named `test_*` in snake_case. Tests usually assert on generated SQL (`toSql()`/bindings) and/or on query results.

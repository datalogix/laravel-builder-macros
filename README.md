# Laravel Builder Macros

[![Latest Stable Version](https://poser.pugx.org/datalogix/laravel-builder-macros/version)](https://packagist.org/packages/datalogix/laravel-builder-macros)
[![Total Downloads](https://poser.pugx.org/datalogix/laravel-builder-macros/downloads)](https://packagist.org/packages/datalogix/laravel-builder-macros)
[![tests](https://github.com/datalogix/laravel-builder-macros/workflows/tests/badge.svg)](https://github.com/datalogix/laravel-builder-macros/actions)
[![StyleCI](https://github.styleci.io/repos/316761194/shield?style=flat)](https://github.styleci.io/repos/316761194)
[![codecov](https://codecov.io/gh/datalogix/laravel-builder-macros/branch/main/graph/badge.svg)](https://codecov.io/gh/datalogix/laravel-builder-macros)
[![License](https://poser.pugx.org/datalogix/laravel-builder-macros/license)](https://packagist.org/packages/datalogix/laravel-builder-macros)

> A set of useful Laravel builder macros.

## Installation

You can install the package via composer:

```bash
composer require datalogix/laravel-builder-macros
```

The package will automatically register itself.

## Macros

- [`addSubSelect`](#addSubSelect)
- [`defaultSelectAll`](#defaultSelectAll)
- [`filter`](#filter)
- [`joinRelation`](#joinRelation)
- [`leftJoinRelation`](#leftJoinRelation)
- [`map`](#map)
- [`whereLike`](#whereLike)

### `addSubSelect`

Add a select sub query.

```php
// Params: $column, $query (Eloquent or query builder)
$query->addSubSelect('primary_address_id',
    Address::select('id')
        ->where('user_id', $user->id)
        ->primary()
);

// It adds primary_address_id to the result set
```

### `defaultSelectAll`

It selects all columns from the query. Useful for queries with joins and additional selects.

```php
$query->defaultSelectAll()
    ->join('contacts', 'users.id', '=', 'contacts.user_id')
    ->addSelect('contacts.name as contact_name');
```

### `filter`

Filter in your models. Each filter is applied with [`whereLike`](#whereLike), and blank values are ignored.

```php
$query->filter(['name' => 'john'])->get();

// Returns all results where name includes `john`
```

You can also supply multiple filters:

```php
$query->filter(['name' => 'john', 'contact.email' => '@'])->get();

// Returns all results where name includes `john` and contact.email includes `@`
```

When filtering with request input, pass the allowed columns as the second argument:

```php
$query->filter($request->all(), ['name', 'email', 'contact.email'])->get();

// Or
$query->filter($request->only(['name', 'email', 'contact.email']))->get();
```

> [!WARNING]
> Never pass unfiltered input such as `$request->all()` without allowed columns: every key becomes a column (or a relation, when it contains a `.`), so users could filter on any column, like `password`, or break the query with keys like `page`.

### `joinRelation`

A query way to join relations.

```php
// Params: $relationName, $operator
$query->joinRelation('contact');
```

Supports `BelongsTo`, `HasOne` and `HasMany` relations.

When no columns are selected, it selects only the columns of the main table (see [`defaultSelectAll`](#defaultSelectAll)), so columns of the joined table like `id` don't override the model attributes. Constraints defined in the relation (`where`, `latestOfMany`, soft deletes, etc.) are not applied to the join. Joining a `HasMany` relation returns the main model once for each related row.

Relations to the same table, like `parent` or `children`, are joined under the relation name, so its columns are referenced through it:

```php
$query->joinRelation('parent')->addSelect('parent.name as parent_name');
```

### `leftJoinRelation`

A query to left join relations.

```php
// Params: $relationName, $operator
$query->leftJoinRelation('contact');
```

It works like [`joinRelation`](#joinRelation), with a `LEFT JOIN`.

### `map`

A direct method to retrieve the results and map it.

```php
$userIds = $query->where('user_id', 10)->map(function ($user) {
    return $user->id;
});

// Returns a collection
```

### `whereLike`

Search in your models with the `LIKE` operator.

> [!NOTE]
> Since Laravel 11.17 the query builder has a native `whereLike($column, $value, $caseSensitive = false)` method. On Eloquent builders this macro takes precedence over it, with a different behavior: the value is wrapped with `%` and the third argument is `$start`, not `$caseSensitive`. On `DB::table()` queries the native method is used.

```php
$query->whereLike('title', 'john')->get();

// Returns all results where title includes `john`
```

```php
$query->whereLike('title', 'john', false)->get();

// Returns all results where title starts with `john`
```

```php
$query->whereLike('title', 'john', true, false)->get();

// Returns all results where title ends with `john`
```

You can also supply an array of columns to search in:

```php
$query->whereLike(['title', 'contact.name'], 'john')->get();

// Returns all results where title or contact.name includes `john`
```

Notes:

- Columns containing a `.` are searched in relations (`contact.name` searches `name` in the `contact` relation, and `posts.comments.body` searches nested relations). Table-prefixed columns like `users.name` are not supported. Relation searches don't support aliased tables (`from('users as u')`), as Laravel's `whereHas` doesn't.
- Columns ending in `_id` are compared with `=` instead of `LIKE`.
- Blank and boolean values are ignored.
- `%` and `_` in the value are not escaped, so they act as wildcards.

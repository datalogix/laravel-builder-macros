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
// Params: $column, $query
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

Filter in your models.

```php
$query->filter(['name' => 'john'])->get();

// Returns all results where name includes `john`
```

You can also supply an array of columns to filter in:
```php
$query->filter(['name' => 'john', 'contact.email' => '@'])->get();

// Returns all results where name includes `john` or contact.email includes `@`
```

You can use `$request->all()`:
```php
$query->filter($request->all())->get();
```

### `joinRelation`

A query way to join relations.

```php
// Params: $relationName, $operator
$query->joinRelation('contact');
```

### `leftJoinRelation`

A query to left join relations.

```php
// Params: $relationName, $operator
$query->leftJoinRelation('contact');
```

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

```php
$query->whereLike('title', 'john')->get();

// Returns all results where title includes `john`
```

You can also supply an array of columns to search in:
```php
$query->whereLike(['title', 'contact.name'], 'john')->get();

// Returns all results where title or contact.name includes `john`
```

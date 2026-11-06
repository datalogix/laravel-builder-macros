## Laravel Builder Macros

This package (`datalogix/laravel-builder-macros`) adds macros to the Eloquent `Builder`: `addSubSelect`, `defaultSelectAll`, `filter`, `joinRelation`, `leftJoinRelation`, `map`, `search` and `whereLike`. They are registered automatically; if a macro with the same name is already registered, it is kept.

### Searching with `search` / `whereLike`

- Prefer `search()`. On Eloquent builders `whereLike()` is this macro, not Laravel's native `whereLike($column, $value, $caseSensitive)`: it wraps the value with `%` and its third argument is `$start`. On `DB::table()` the native method is used.
- Arguments: `$columns`, `$value`, `$start = true`, `$end = true`, `$escape = false`, `$ignoreCase = false`. Pass `escape: true` to match `%`, `_` and `!` literally, and `ignoreCase: true` to ignore case on PostgreSQL (`ILIKE`).
- Columns with a `.` search a relation (`posts.title`, `posts.comments.body`); table-prefixed columns like `users.name` are not supported. Columns ending in `_id` use `=`. Blank and boolean values add no condition.

@verbatim
<code-snippet name="Search columns and relations" lang="php">
Post::query()->search(['title', 'user.name'], $term, escape: true)->get();
</code-snippet>
@endverbatim

### Filtering request input with `filter`

Always pass the allowed columns when the filters come from the request. Every key becomes a column or, with a `.`, a relation, and Eloquent resolves a relation by calling the model method of the same name.

@verbatim
<code-snippet name="Filter request input" lang="php">
User::filter($request->all(), ['name', 'email', 'contact.email'], true)->paginate();
</code-snippet>
@endverbatim

### Joining relations with `joinRelation` / `leftJoinRelation`

- Supports `BelongsTo`, `HasOne`, `HasMany`, `MorphOne` and `MorphMany`, including nested paths (`posts.comments`). `MorphTo`, `BelongsToMany` and `*Through` relations throw an `InvalidArgumentException`.
- When no columns are selected, only the main table columns are selected, so add the joined columns with `addSelect()`.
- A relation to the main table or to an already joined table is joined under the relation name (`parent.name`, `editor.name`).
- Morph type and `SoftDeletes` constraints are applied; other relation constraints (`where`, `latestOfMany`) are not. `HasMany` joins repeat the main model for each related row.

@verbatim
<code-snippet name="Join relations and select their columns" lang="php">
Post::joinRelation('user')
    ->leftJoinRelation('editor')
    ->addSelect('users.name as author_name', 'editor.name as editor_name')
    ->get();
</code-snippet>
@endverbatim

### Other macros

- `addSubSelect($column, $query)` adds `$query` as a sub select limited to one row, keeping the main table columns.
- `defaultSelectAll()` selects `table.*` when no columns are selected yet.
- `map($callback)` runs the query and returns the mapped `Collection`; it is not chainable.

<?php

namespace Datalogix\BuilderMacros\Macros;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @param  array|string  $columns
 * @param  mixed  $value
 * @return \Illuminate\Database\Eloquent\Builder
 */
class WhereLike
{
    public function __invoke()
    {
        return function ($columns, $value) {
            $this->where(function (Builder $query) use ($columns, $value) {
                foreach (Arr::wrap($columns) as $column) {
                    $query->when(
                        Str::contains($column, '.'),

                        // Relational searches
                        function (Builder $query) use ($column, $value) {
                            $parts = explode('.', $column);
                            $relationColumn = array_pop($parts);
                            $relationName = join('.', $parts);

                            return $query->orWhereHas(
                                $relationName,
                                function (Builder $query) use ($relationColumn, $value) {
                                    if (Str::endsWith($relationColumn, '_id')) {
                                        $query->where($relationColumn, $value);
                                    } else {
                                        $query->where($relationColumn, 'LIKE', "%{$value}%");
                                    }
                                }
                            );
                        },

                        // Default searches
                        function (Builder $query) use ($column, $value) {
                            if (Str::endsWith($column, '_id')) {
                                return $query->orWhere($column, $value);
                            }

                            return $query->orWhere($column, 'LIKE', "%{$value}%");
                        }
                    );
                }
            });

            return $this;
        };
    }
}

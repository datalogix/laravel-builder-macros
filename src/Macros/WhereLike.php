<?php

namespace Datalogix\BuilderMacros\Macros;

use Datalogix\BuilderMacros\Support\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * @mixin Builder
 *
 * @param  array|string  $columns
 * @param  mixed  $value
 * @return Builder
 */
class WhereLike
{
    public function __invoke()
    {
        return function ($columns, $value, $start = true, $end = true) {
            $start = $start === true ? '%' : $start;
            $end = $end === true ? '%' : $end;

            // Booleans would be cast to "" or "1" and match almost every row
            if (is_bool($value) || blank($value)) {
                return $this;
            }

            $from = Table::reference($this->getQuery()->from);

            $this->where(function (Builder $query) use ($from, $columns, $value, $start, $end) {
                foreach (Arr::wrap($columns) as $column) {
                    $query->when(
                        Str::contains($column, '.'),

                        // Relational searches
                        function (Builder $query) use ($column, $value, $start, $end) {
                            $parts = explode('.', $column);
                            $relationColumn = array_pop($parts);
                            $relationName = implode('.', $parts);

                            return $query->orWhereHas(
                                $relationName,
                                function (Builder $query) use ($relationColumn, $value, $start, $end) {
                                    if (Str::endsWith($relationColumn, '_id')) {
                                        $query->where($relationColumn, $value);
                                    } else {
                                        $query->where($relationColumn, 'LIKE', $start.$value.$end);
                                    }
                                }
                            );
                        },

                        // Default searches
                        function (Builder $query) use ($from, $column, $value, $start, $end) {
                            if (Str::endsWith($column, '_id')) {
                                return $query->orWhere(($from ? $from.'.' : '').$column, $value);
                            }

                            return $query->orWhere(($from ? $from.'.' : '').$column, 'LIKE', $start.$value.$end);
                        }
                    );
                }
            });

            return $this;
        };
    }
}

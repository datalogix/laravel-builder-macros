<?php

namespace Datalogix\BuilderMacros\Macros;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @param  array  $filters
 * @return \Illuminate\Database\Eloquent\Builder
 */
class Filter
{
    public function __invoke()
    {
        return function ($filters) {
            foreach (Arr::wrap($filters) as $column => $filter) {
                $this->when($filter, function (Builder $query, $filter) use ($column) {
                    return $query->whereLike($column, $filter);
                });
            }

            return $this;
        };
    }
}

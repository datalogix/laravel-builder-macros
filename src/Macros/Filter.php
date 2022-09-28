<?php

namespace Datalogix\BuilderMacros\Macros;

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
                $this->whereLike($column, $filter);
            }

            return $this;
        };
    }
}

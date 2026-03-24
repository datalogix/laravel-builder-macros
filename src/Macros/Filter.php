<?php

namespace Datalogix\BuilderMacros\Macros;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/**
 * @mixin Builder
 *
 * @param  array  $filters
 * @return Builder
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

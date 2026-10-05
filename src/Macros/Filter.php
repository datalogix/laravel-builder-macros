<?php

namespace Datalogix\BuilderMacros\Macros;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

/**
 * @mixin Builder
 *
 * @param  array  $filters
 * @param  array|null  $allowed
 * @return Builder
 */
class Filter
{
    public function __invoke()
    {
        return function ($filters, ?array $allowed = null) {
            $filters = Arr::wrap($filters);

            if (! is_null($allowed)) {
                $filters = Arr::only($filters, $allowed);
            }

            foreach ($filters as $column => $filter) {
                // Numeric keys aren't columns, e.g. filter('john') or filter(['john'])
                if (is_string($column) && (is_scalar($filter) || is_null($filter))) {
                    $this->whereLike($column, $filter);
                }
            }

            return $this;
        };
    }
}

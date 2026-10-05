<?php

namespace Datalogix\BuilderMacros\Macros;

use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Builder
 *
 * @param  string  $column
 * @param  Builder|\Illuminate\Database\Query\Builder  $query
 * @return Builder
 */
class AddSubSelect
{
    public function __invoke()
    {
        return function ($column, $query) {
            $this->defaultSelectAll();

            return $this->selectSub((clone $query)->limit(1), $column);
        };
    }
}

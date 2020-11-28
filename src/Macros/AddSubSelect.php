<?php

namespace Datalogix\BuilderMacros\Macros;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @param  string  $column
 * @param  \Illuminate\Database\Query\Builder  $query
 * @return \Illuminate\Database\Query\Builder
 */
class AddSubSelect
{
    public function __invoke()
    {
        return function ($column, $query) {
            $this->defaultSelectAll();

            return $this->selectSub($query->limit(1)->getQuery(), $column);
        };
    }
}

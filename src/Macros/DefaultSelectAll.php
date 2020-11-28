<?php

namespace Datalogix\BuilderMacros\Macros;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @return \Illuminate\Database\Eloquent\Builder
 */
class DefaultSelectAll
{
    public function __invoke()
    {
        return function () {
            if (is_null($this->getQuery()->columns)) {
                $this->select($this->getQuery()->from.'.*');
            }

            return $this;
        };
    }
}

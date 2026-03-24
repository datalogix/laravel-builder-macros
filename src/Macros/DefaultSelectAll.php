<?php

namespace Datalogix\BuilderMacros\Macros;

use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Builder
 *
 * @return Builder
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

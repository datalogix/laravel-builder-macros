<?php

namespace Datalogix\BuilderMacros\Macros;

use Datalogix\BuilderMacros\Support\Table;
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
            $from = Table::reference($this->getQuery()->from);

            if (is_null($this->getQuery()->columns) && $from) {
                $this->select($from.'.*');
            }

            return $this;
        };
    }
}

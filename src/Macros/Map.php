<?php

namespace Datalogix\BuilderMacros\Macros;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * @mixin Builder
 *
 * @param  callable  $callback
 * @return Collection
 */
class Map
{
    public function __invoke()
    {
        return function (callable $callback) {
            return $this->get()->map($callback);
        };
    }
}

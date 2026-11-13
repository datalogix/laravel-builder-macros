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
        return fn (callable $callback) => $this->get()->map($callback);
    }
}

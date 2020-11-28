<?php

namespace Datalogix\BuilderMacros\Macros;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @param  callable  $callback
 * @return \Illuminate\Support\Collection
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

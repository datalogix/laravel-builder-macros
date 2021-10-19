<?php

namespace Datalogix\BuilderMacros;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider;

class BuilderMacroServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        Collection::make($this->macros())
            ->reject(function ($class, $macro) {
                return Builder::hasGlobalMacro($macro);
            })
            ->each(function ($class, $macro) {
                Builder::macro($macro, app($class)());
            });
    }

    /**
     * Returns macros to be registered.
     *
     * @return array
     */
    private function macros()
    {
        return [
            'addSubSelect' => \Datalogix\BuilderMacros\Macros\AddSubSelect::class,
            'defaultSelectAll' => \Datalogix\BuilderMacros\Macros\DefaultSelectAll::class,
            'filter' => \Datalogix\BuilderMacros\Macros\Filter::class,
            'joinRelation' => \Datalogix\BuilderMacros\Macros\JoinRelation::class,
            'leftJoinRelation' => \Datalogix\BuilderMacros\Macros\LeftJoinRelation::class,
            'map' => \Datalogix\BuilderMacros\Macros\Map::class,
            'whereLike' => \Datalogix\BuilderMacros\Macros\WhereLike::class,
        ];
    }
}

<?php

namespace Datalogix\BuilderMacros;

use Datalogix\BuilderMacros\Macros\AddSubSelect;
use Datalogix\BuilderMacros\Macros\DefaultSelectAll;
use Datalogix\BuilderMacros\Macros\Filter;
use Datalogix\BuilderMacros\Macros\JoinRelation;
use Datalogix\BuilderMacros\Macros\LeftJoinRelation;
use Datalogix\BuilderMacros\Macros\Map;
use Datalogix\BuilderMacros\Macros\WhereLike;
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
            'addSubSelect' => AddSubSelect::class,
            'defaultSelectAll' => DefaultSelectAll::class,
            'filter' => Filter::class,
            'joinRelation' => JoinRelation::class,
            'leftJoinRelation' => LeftJoinRelation::class,
            'map' => Map::class,
            'whereLike' => WhereLike::class,
        ];
    }
}

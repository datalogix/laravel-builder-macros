<?php

namespace Datalogix\BuilderMacros\Macros;

use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Builder
 *
 * @param  string  $relationName
 * @param  string  $operator
 * @return Builder
 */
class LeftJoinRelation extends JoinRelation
{
    /**
     * The type of join.
     *
     * @var string
     */
    protected $type = 'left';
}

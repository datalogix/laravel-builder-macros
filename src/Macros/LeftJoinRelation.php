<?php

namespace Datalogix\BuilderMacros\Macros;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 *
 * @param  string  $relationName
 * @param  string  $operator
 * @return mixed
 */
class LeftJoinRelation
{
    public function __invoke()
    {
        return function ($relationName, $operator = '=') {
            $relation = $this->getRelation($relationName);

            return $this->leftJoin(
                $relation->getRelated()->getTable(),
                $relation->getQualifiedForeignKeyName(),
                $operator,
                $relation->getQualifiedOwnerKeyName()
            );
        };
    }
}

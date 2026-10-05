<?php

namespace Datalogix\BuilderMacros\Macros;

use Datalogix\BuilderMacros\Support\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use InvalidArgumentException;

/**
 * @mixin Builder
 *
 * @param  string  $relationName
 * @param  string  $operator
 * @return Builder
 */
class JoinRelation
{
    /**
     * The type of join.
     *
     * @var string
     */
    protected $type = 'inner';

    public function __invoke()
    {
        $type = $this->type;

        return function ($relationName, $operator = '=') use ($type) {
            $relation = $this->getRelation($relationName);

            if ($relation instanceof BelongsTo) {
                $firstKey = $relation->getForeignKeyName();
                $secondKey = $relation->getOwnerKeyName();
            } elseif ($relation instanceof HasOneOrMany) {
                $firstKey = $relation->getLocalKeyName();
                $secondKey = $relation->getForeignKeyName();
            } else {
                throw new InvalidArgumentException(sprintf(
                    'Relation [%s] of type [%s] is not supported. Only BelongsTo, HasOne and HasMany relations can be joined.',
                    $relationName,
                    get_class($relation)
                ));
            }

            $from = Table::reference($this->getQuery()->from) ?? $this->getModel()->getTable();
            $table = $related = $relation->getRelated()->getTable();

            // Self relations, like "parent" or "children", join the same table under the relation name
            if ($related === $from) {
                $table = $related.' as '.$relationName;
                $related = $relationName;
            }

            // Avoid columns of the joined table, like "id", overriding the model attributes
            $this->defaultSelectAll();

            return $this->join($table, $from.'.'.$firstKey, $operator, $related.'.'.$secondKey, $type);
        };
    }
}

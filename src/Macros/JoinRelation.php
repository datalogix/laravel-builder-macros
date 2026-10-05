<?php

namespace Datalogix\BuilderMacros\Macros;

use Datalogix\BuilderMacros\Support\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
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
            $from = Table::reference($this->getQuery()->from) ?? $this->getModel()->getTable();
            $parent = $from;
            $relation = null;

            // Nested relations, like "posts.comments", join each relation from the previous one
            foreach (explode('.', $relationName) as $name) {
                $relation = $relation
                    ? $relation->getRelated()->newQuery()->getRelation($name)
                    : $this->getRelation($name);

                $morphType = $morphClass = null;

                // MorphTo extends BelongsTo, but its related table is only known per row
                if ($relation instanceof BelongsTo && ! $relation instanceof MorphTo) {
                    $firstKey = $relation->getForeignKeyName();
                    $secondKey = $relation->getOwnerKeyName();
                } elseif ($relation instanceof HasOneOrMany) {
                    $firstKey = $relation->getLocalKeyName();
                    $secondKey = $relation->getForeignKeyName();

                    if ($relation instanceof MorphOneOrMany) {
                        $morphType = $relation->getMorphType();
                        $morphClass = $relation->getMorphClass();
                    }
                } else {
                    throw new InvalidArgumentException(sprintf(
                        'Relation [%s] of type [%s] is not supported. Only BelongsTo, HasOne, HasMany, MorphOne and MorphMany relations can be joined.',
                        $name,
                        get_class($relation)
                    ));
                }

                $model = $relation->getRelated();
                $table = $related = $model->getTable();

                $used = array_map(function ($join) {
                    return Table::reference($join->table);
                }, $this->getQuery()->joins ?? []);

                // Self relations, like "parent" or "children", and relations to an already joined table,
                // like "author" and "editor", join the table under the relation name
                if ($related === $from || in_array($related, $used, true)) {
                    $table = $related.' as '.$name;
                    $related = $name;
                }

                $deletedAt = in_array(SoftDeletes::class, class_uses_recursive($model))
                    ? $model->getDeletedAtColumn()
                    : null;

                // Avoid columns of the joined table, like "id", overriding the model attributes
                $this->defaultSelectAll();

                $this->join($table, function ($join) use ($parent, $firstKey, $operator, $related, $secondKey, $morphType, $morphClass, $deletedAt) {
                    $join->on($parent.'.'.$firstKey, $operator, $related.'.'.$secondKey);

                    // Polymorphic relations only match rows of the model type
                    if ($morphType) {
                        $join->where($related.'.'.$morphType, '=', $morphClass);
                    }

                    // Soft deleted rows are not joined, as in the relation
                    if ($deletedAt) {
                        $join->whereNull($related.'.'.$deletedAt);
                    }
                }, null, null, $type);

                $parent = $related;
            }

            return $this;
        };
    }
}

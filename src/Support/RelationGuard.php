<?php

namespace Datalogix\BuilderMacros\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use ReflectionClass;
use ReflectionMethod;

class RelationGuard
{
    /**
     * Ensure every name of a relation path, like "posts.comments", is a relation of the model.
     *
     * Eloquent resolves a relation by calling the model method of the same name, so a name taken from
     * user input, like "truncate" or "save", would run that method. Names are checked before any call.
     *
     * @param  string  $path
     * @return void
     *
     * @throws RelationNotFoundException
     */
    public static function check(Model $model, $path)
    {
        foreach (explode('.', $path) as $name) {
            if (! static::isRelationMethod($model, $name)) {
                throw RelationNotFoundException::make($model, $name);
            }

            $relation = Relation::noConstraints(function () use ($model, $name) {
                return $model->newInstance()->$name();
            });

            if (! $relation instanceof Relation) {
                throw RelationNotFoundException::make($model, $name);
            }

            $model = $relation->getRelated();
        }
    }

    /**
     * Determine if the name can be a relation method, without calling it.
     *
     * @param  string  $name
     * @return bool
     */
    protected static function isRelationMethod(Model $model, $name)
    {
        if ($model->relationResolver(get_class($model), $name)) {
            return true;
        }

        if (! method_exists($model, $name)) {
            return false;
        }

        $method = new ReflectionMethod($model, $name);

        if (! $method->isPublic() || $method->isStatic() || $method->getNumberOfRequiredParameters() > 0) {
            return false;
        }

        // Methods of Eloquent itself, including its traits like SoftDeletes, are never relations
        $eloquent = dirname((new ReflectionClass(Model::class))->getFileName()).DIRECTORY_SEPARATOR;

        return ! str_starts_with((string) $method->getFileName(), $eloquent);
    }
}

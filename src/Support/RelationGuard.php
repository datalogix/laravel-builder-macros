<?php

namespace Datalogix\BuilderMacros\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\RelationNotFoundException;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;

class RelationGuard
{
    /**
     * The directory of the Laravel framework sources, like ".../src/Illuminate/".
     *
     * @var string|null
     */
    protected static $framework;

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
     * @throws InvalidArgumentException
     */
    public static function check(Model $model, $path)
    {
        $names = explode('.', $path);

        foreach ($names as $index => $name) {
            if (! static::isRelationMethod($model, $name)) {
                throw RelationNotFoundException::make($model, $name);
            }

            $relation = Relation::noConstraints(function () use ($model, $name) {
                return $model->newInstance()->$name();
            });

            if (! $relation instanceof Relation) {
                throw RelationNotFoundException::make($model, $name);
            }

            // The related model of a MorphTo is only known per row, so the next names can't be checked
            if ($relation instanceof MorphTo && $index < count($names) - 1) {
                throw new InvalidArgumentException(sprintf(
                    'Relation [%s] is a MorphTo relation, which can\'t be followed by other relations in [%s].',
                    $name,
                    $path
                ));
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

        // Methods of Eloquent's Model are never relations, even when the model overrides them, like save()
        if (! method_exists($model, $name) || method_exists(Model::class, $name)) {
            return false;
        }

        $method = new ReflectionMethod($model, $name);

        if (! $method->isPublic() || $method->isStatic() || $method->getNumberOfRequiredParameters() > 0) {
            return false;
        }

        // Methods of Laravel's own traits, like MustVerifyEmail::markEmailAsVerified(), are not relations either
        static::$framework ??= dirname((new ReflectionClass(Model::class))->getFileName(), 3).DIRECTORY_SEPARATOR;

        return ! str_starts_with((string) $method->getFileName(), static::$framework);
    }
}

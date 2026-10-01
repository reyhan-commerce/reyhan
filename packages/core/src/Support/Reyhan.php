<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

final class Reyhan
{
    /**
     * Runtime model registry bindings.
     *
     * @var array<string, class-string<Model>>
     */
    private static array $modelBindings = [];

    /**
     * Get the current Reyhan engine version.
     */
    public static function version(): string
    {
        return (string) config('reyhan.version', '1.0.0');
    }

    /**
     * Bind a custom user model to replace a core model.
     *
     * @param  string  $alias  e.g. 'product', 'order'
     * @param  class-string<Model>  $concrete
     */
    public static function useModel(string $alias, string $concrete): void
    {
        if (! is_subclass_of($concrete, Model::class)) {
            throw new InvalidArgumentException("Class [{$concrete}] must extend Illuminate\\Database\\Eloquent\\Model.");
        }

        self::$modelBindings[$alias] = $concrete;
    }

    /**
     * Resolve the configured Eloquent model class for a given alias.
     *
     * @param  string  $alias  e.g. 'product', 'order', 'cart'
     * @return class-string<Model>
     */
    public static function model(string $alias): string
    {
        if (isset(self::$modelBindings[$alias])) {
            return self::$modelBindings[$alias];
        }

        $configured = config("reyhan.models.{$alias}");

        if (is_string($configured) && class_exists($configured)) {
            return $configured;
        }

        throw new InvalidArgumentException("No model configured for Reyhan alias [{$alias}].");
    }

    /**
     * Instantiate a new model instance for the given alias.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function newModel(string $alias, array $attributes = []): Model
    {
        $class = self::model($alias);

        return new $class($attributes);
    }

    /**
     * Start a new query builder on the resolved model alias.
     */
    public static function query(string $alias): Builder
    {
        /** @var class-string<Model> $class */
        $class = self::model($alias);

        return $class::query();
    }
}

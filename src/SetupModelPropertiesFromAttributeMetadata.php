<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

/**
 * To be added on model classes that have casts or relations defined in their metadata.
 *
 * @method static getMetadata(): \FlorentPoujol\LaravelModelMetadata\ModelMetadata
 */
trait SetupModelPropertiesFromAttributeMetadata
{
    /** @var null|array<string, mixed> */
    protected static $defaultValues;

    public static function bootSetupModelPropertiesFromAttributeMetadata(): void
    {
        static::compileDefaultValuesFromMetadata();
    }

    protected static function compileDefaultValuesFromMetadata(): void
    {
        static::$staticFillable = array_merge(
            static::getMetadata()->getDefaultValues(),
            // default values already set on the model takes precedence
            (new static())->attributes // property is protected but this is allowed since we are inside the model class
        );
    }

    // --------------------------------------------------
    // Fillable

    /** @var null|array<string> */
    protected static $staticFillable;

    /**
     * @return array<string>
     */
    public function getFillable(): array
    {
        if (static::$staticFillable === null) {
            static::compileFillableFromMetadata();
        }

        return static::$staticFillable;
    }

    protected static function compileFillableFromMetadata(): void
    {
        static::$staticFillable = array_values(array_unique(array_merge(
            (new static())->fillable,
            static::getMetadata()->getFillable()
        )));
    }

    // --------------------------------------------------
    // Guarded

    /** @var null|array<string> */
    protected static $staticGuarded;

    /**
     * @return array<string>
     */
    public function getGuarded(): array
    {
        if (static::$staticGuarded === null) {
            static::compileGuardedFromMetadata();
        }

        return static::$staticGuarded;
    }

    protected static function compileGuardedFromMetadata(): void
    {
        static::$staticGuarded = array_values(array_unique(array_merge(
            (new static())->guarded,
            static::getMetadata()->getGuarded()
        )));
    }

    // --------------------------------------------------
    // Hidden

    /** @var null|array<string> */
    protected static $staticHidden;

    /**
     * @return array<string>
     */
    public function getHidden(): array
    {
        if (static::$staticHidden === null) {
            static::compileHiddenFromMetadata();
        }

        return static::$staticHidden;
    }

    protected static function compileHiddenFromMetadata(): void
    {
        static::$staticHidden = array_values(array_unique(array_merge(
            (new static())->hidden,
            static::getMetadata()->getHidden()
        )));
    }

    // --------------------------------------------------
    // Hidden

    /** @var null|array<string> */
    protected static $staticDates;

    /**
     * @return array<string>
     */
    public function getDates(): array
    {
        if (static::$staticDates === null) {
            static::compileDatesFromMetadata();
        }

        return static::$staticDates;
    }

    protected static function compileDatesFromMetadata(): void
    {
        $model = new static();
        $defaults = $model->usesTimestamps() ? [
            static::CREATED_AT,
            static::UPDATED_AT,
        ] : [];

        static::$staticDates = array_values(array_unique(array_merge(
            $defaults,
            (new static())->dates,
            static::getMetadata()->getDates()
        )));
    }

    // --------------------------------------------------
    // Casts

    /** @var array<string, string> */
    protected static $staticCastTypes;

    public function getCastType(string $attribute): string
    {
        if (static::$staticCasts === null) {
            static::compileCasts();
        }

        return static::$staticCastTypes[$attribute];
    }

    /** @var array<string, string|object> */
    protected static $staticCasts;

    public function getCasts(): array
    {
        if (static::$staticCasts === null) {
            static::compileCasts();
        }

        return static::$staticCasts;
    }

    protected static function compileCasts(): void
    {
        static::$staticCasts = array_merge(
            static::getMetadata()->getCasts(),
            (new static())->casts
        );

        static::$staticCastTypes = [];
        foreach (static::$staticCasts as $attribute => $cast) {
            if (
                strncmp($cast, 'date:', 5) === 0 ||
                strncmp($cast, 'datetime:', 9) === 0
            ) {
                $cast = 'custom_datetime';
            } elseif (strncmp($cast, 'decimal:', 8) === 0) {
                $cast = 'decimal';
            }

            static::$staticCastTypes[$attribute] = trim(strtolower($cast));
        }
    }
}


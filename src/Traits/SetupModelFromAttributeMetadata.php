<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributeMetadata\Traits;

use FlorentPoujol\LaravelAttributeMetadata\AttributeMetadata;

/**
 * To be added on model classes that have relations defined in their metadata.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \FlorentPoujol\LaravelAttributeMetadata\Traits\HasAttributesMetadata
 */
trait SetupModelFromAttributeMetadata
{
    /** @var null|array<string, mixed> */
    protected static $defaultValues;

    public static function bootSetupModelPropertiesFromAttributeMetadata(): void
    {
        static::compileDefaultValuesFromMetadata();
    }

    protected static function compileDefaultValuesFromMetadata(): void
    {
        static::$defaultValues = array_merge(
            static::getAttributeMetadataCollection()->getDefaultValues(),
            // default values already set on the model takes precedence
            (new static())->attributes // property is protected but this is allowed since we are inside the model class
        );
    }

    /**
     * @return array<string, mixed> The attributes that have a default values and their values
     */
    public function getDefaultValues(): array
    {
        return static::getAttributeMetadataCollection()
            ->filter(function (AttributeMetadata $meta) {
                return $meta->hasDefaultValue();
            })
            ->mapWithKeys(function (AttributeMetadata $meta, string $key) {
                return [$key => $meta->getDefaultValue()];
            })
            ->toArray();
    }

    // /**
    //  * @return array<string> The fillable attributes
    //  */
    // public function getFillable(): array
    // {
    //     return static::getAttributeMetadataCollection()
    //         ->filter(function (AttributeMetadata $meta) {
    //             return $meta->isFillable();
    //         })
    //         ->keys()
    //         ->toArray();
    // }
    //
    // /**
    //  * @return array<string> The guarded attributes
    //  */
    // public function getGuarded(): array
    // {
    //     return static::getAttributeMetadataCollection()
    //         ->filter(function (AttributeMetadata $meta) {
    //             return $meta->isGuarded();
    //         })
    //         ->keys()
    //         ->toArray();
    // }
    //
    // /**
    //  * @return array<string> The hidden attributes
    //  */
    // public function getHidden(): array
    // {
    //     return static::getAttributeMetadataCollection()
    //         ->filter(function (AttributeMetadata $meta) {
    //             return $meta->isHidden();
    //         })
    //         ->keys()
    //         ->toArray();
    // }
    //
    // /**
    //  * @return array<string> The dates attributes
    //  */
    // public function getDates(): array
    // {
    //     return static::getAttributeMetadataCollection()
    //         ->filter(function (AttributeMetadata $meta) {
    //             return $meta->isDate();
    //         })
    //         ->keys()
    //         ->toArray();
    // }

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
            static::getAttributeMetadataCollection()->getFillable()
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
            static::getAttributeMetadataCollection()->getGuarded()
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
            static::getAttributeMetadataCollection()->getHidden()
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
            static::getAttributeMetadataCollection()->getDates()
        )));
    }

    // --------------------------------------------------
    // Casts

    /** @var null|array<string, string> */
    protected static $staticCastTypes;

    /**
     * @param string $key
     *
     * @return string
     */
    public function getCastType(string $attribute)
    {
        if (static::$staticCasts === null) {
            static::compileCasts();
        }

        return static::$staticCastTypes[$attribute];
    }

    /** @var null|array<string, string|object> */
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
            (new static())->casts,
            static::getAttributeMetadataCollection()->getCasts()
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

    // --------------------------------------------------
    // Primary key

    /** @var null|bool */
    protected static $staticIncrementing;

    /** @var null|string 'int' or 'string' */
    protected static $staticKeyType;

    /** @var null|string */
    protected static $staticKeyName;

    public function getIncrementing(): bool
    {
        if (static::$staticIncrementing === null) {
            static::compilePrimaryKeyInfo();
        }

        return static::$staticIncrementing;
    }

    public function getKeyType(): string
    {
        if (static::$staticKeyType === null) {
            static::compilePrimaryKeyInfo();
        }

        return static::$staticKeyType;
    }

    public function getKeyName(): string
    {
        if (static::$staticKeyName === null) {
            static::compilePrimaryKeyInfo();
        }

        return static::$staticKeyName;
    }

    protected function compilePrimaryKeyInfo(): void
    {
        $model = new static();
        static::$staticIncrementing = $model->incrementing;
        static::$staticKeyType = $model->keyType;
        static::$staticKeyName = $model->primaryKey;

        /** @var \FlorentPoujol\LaravelAttributeMetadata\AttributeMetadata $primaryKeyMeta */
        $primaryKeyMeta = static::getAttributeMetadataCollection()->getPrimaryKeyMeta();
        if ($primaryKeyMeta === null) {
            return;
        }

        static::$staticIncrementing = $primaryKeyMeta->isIncrementingPrimaryKey();
        static::$staticKeyType = $primaryKeyMeta->getPrimaryKeyType();
        static::$staticKeyName = $primaryKeyMeta->getName() ?: $model->primaryKey;
    }
}


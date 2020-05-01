<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\ModelProperties;

use FlorentPoujol\LaravelAttributePresets\BasePreset;

/**
 * To be added on model classes that have relations defined in their preset.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \FlorentPoujol\LaravelAttributePresets\HasAttributesMetadata
 */
trait SetupModelFromAttributeMetadata
{
    /** @var null|array<string, mixed> */
    protected static $defaultValues;

    public static function bootSetupModelFromAttributeMetadata(): void
    {
        static::compileDefaultValuesFromMetadata();
    }

    protected static function compileDefaultValuesFromMetadata(): void
    {
        static::getAttributeConfigCollection()
            ->keep(function (BasePreset $attr) {
                return $attr->getModelPropertiesHandler()->hasDefaultValue();
            })
            ->mapWithKeys(function (BasePreset $attr) {
                return [
                    $attr->getName(),
                    $attr->getModelPropertiesHandler()->getDefaultValue()
                ];
            });

        static::$defaultValues = array_merge(
            static::getAttributeConfigCollection()->getDefaultValues(),
            // default values already set on the model takes precedence
            (new static())->attributes // property is protected but this is allowed since we are inside the model class
        );
    }

    // --------------------------------------------------

    /** @var null|array<string> */
    protected static $staticFillable;

    /**
     * @return array<string>
     */
    public function getFillable(): array // from built-in GuardsAttributes trait
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
            static::getAttributeConfigCollection()->getFillable()
        )));
    }

    // --------------------------------------------------

    /** @var null|array<string> */
    protected static $staticGuarded;

    /**
     * @return array<string>
     */
    public function getGuarded(): array // from built-in GuardsAttributes trait
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
            static::getAttributeConfigCollection()->getGuarded()
        )));
    }

    // --------------------------------------------------

    /** @var null|array<string> */
    protected static $staticHidden;

    /**
     * @return array<string>
     */
    public function getHidden(): array // from built-in HidesAttributes trait
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
            static::getAttributeConfigCollection()->getHidden()
        )));
    }

    // --------------------------------------------------

    /** @var null|array<string> */
    protected static $staticDates;

    /**
     * @return array<string>
     */
    public function getDates(): array // from built-in Model class
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
            static::getAttributeConfigCollection()->getDates()
        )));
    }

    // --------------------------------------------------

    /** @var null|array<string, string> */
    protected static $staticCastTypes;

    /**
     * @param string $key
     *
     * @return string
     */
    public function getCastType(string $attribute) // from built-in HasAttributes trait
    {
        if (static::$staticCasts === null) {
            static::compileCasts();
        }

        return static::$staticCastTypes[$attribute];
    }

    /** @var null|array<string, string|object> */
    protected static $staticCasts;

    public function getCasts(): array // from built-in HasAttributes trait
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
            static::getAttributeConfigCollection()->getCasts()
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

    protected static function compilePrimaryKeyInfo(): void
    {
        $model = new static();
        static::$staticIncrementing = $model->incrementing;
        static::$staticKeyType = $model->keyType;
        static::$staticKeyName = $model->primaryKey;

        /** @var \FlorentPoujol\LaravelAttributePresets\BasePreset $primaryKeyMeta */
        $primaryKeyMeta = static::getAttributeConfigCollection()->getPrimaryKeyMeta();
        if ($primaryKeyMeta === null) {
            return;
        }

        static::$staticIncrementing = $primaryKeyMeta->isIncrementingPrimaryKey();
        static::$staticKeyType = $primaryKeyMeta->getPrimaryKeyType();
        static::$staticKeyName = $primaryKeyMeta->getName() ?: $model->primaryKey;
    }
}


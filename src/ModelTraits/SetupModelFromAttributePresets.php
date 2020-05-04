<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\ModelTraits;

/**
 * To be added on model classes that have relations defined in their preset.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \FlorentPoujol\LaravelAttributePresets\HasAttributePresets
 */
trait SetupModelFromAttributePresets
{
    /** @var null|array<string, mixed> */
    protected static $defaultValues;

    public function initializeSetupModelFromAttributePresets(): void
    {
        if (static::$defaultValues === null) {
            static::$defaultValues = static::getAttributePresetCollection()
                ->getDefaultValues();
        }

        $this->attributes = array_merge(
            static::$defaultValues,
            $this->attributes
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
            static::$staticFillable = array_values(array_unique(array_merge(
                (new static())->fillable,
                static::getAttributePresetCollection()->getFillable()
            )));
        }

        return static::$staticFillable;
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
            static::$staticGuarded = array_values(array_unique(array_merge(
                static::getAttributePresetCollection()->getGuarded(),
                (new static())->guarded
            )));
        }

        return static::$staticGuarded;
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
            static::$staticHidden = array_values(array_unique(array_merge(
                static::getAttributePresetCollection()->getHidden(),
                (new static())->hidden
            )));
        }

        return static::$staticHidden;
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
            $model = new static();
            $defaults = $model->usesTimestamps() ? [
                static::CREATED_AT,
                static::UPDATED_AT,
            ] : [];

            static::$staticDates = array_values(array_unique(array_merge(
                static::getAttributePresetCollection()->getDates(),
                $model->dates,
                $defaults
            )));
        }

        return static::$staticDates;
    }

    // --------------------------------------------------

    /** @var null|array<string, string> */
    protected static $staticCastTypes;

    /**
     * @return string
     */
    public function getCastType(string $attribute) // from built-in HasAttributes trait
    {
        if (static::$staticCasts === null) {
            static::compileCasts();
        }

        return static::$staticCastTypes[$attribute]; // getCastType() is always called with the knowledge that the attribute has a cast
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
            static::getAttributePresetCollection()->getCasts(),
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

        $primaryKeyAttr = static::getAttributePresetCollection()->getPrimaryKeyPreset();
        if ($primaryKeyAttr === null) {
            return;
        }

        static::$staticIncrementing = $primaryKeyAttr->isIncrementingPrimaryKey();
        static::$staticKeyType = $primaryKeyAttr->getPrimaryKeyType();
        static::$staticKeyName = $primaryKeyAttr->getName() ?: $model->primaryKey;
    }
}


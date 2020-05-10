<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\ModelTraits;

use FlorentPoujol\LaravelAttributePresets\BasePreset;
use FlorentPoujol\LaravelAttributePresets\Collection;
use Illuminate\Database\Schema\Blueprint;

/**
 * @method static array getRawAttributePresets() Shall be implemented typically in the model class itself
 */
trait HasAttributePresets
{
    /** @var \FlorentPoujol\LaravelAttributePresets\Collection */
    protected static $attributePresetCollection;

    /**
     * @return \FlorentPoujol\LaravelAttributePresets\Collection
     */
    public static function getAttributePresetCollection(): Collection
    {
        if (static::$attributePresetCollection !== null) {
            return static::$attributePresetCollection;
        }

        $collectionFqcn = Collection::class;
        if (property_exists(static::class, 'attributePresetsCollectionFqcn')) {
            /** @noinspection PhpUndefinedFieldInspection */
            $collectionFqcn = static::$attributePresetsCollectionFqcn;
        }

        static::$attributePresetCollection = new $collectionFqcn(
            static::class, // model Fqcn
            static::getRawAttributePresets() // attribute preset definitions
        );

        return static::$attributePresetCollection;
    }

    public static function getAttributePreset(string $name): ?BasePreset
    {
        $modelMetadata = static::getAttributePresetCollection();

        return $modelMetadata->get($name);
    }

    /**
     * @param string|array<string> $attributes One or several attribute names to restrict the results to
     *
     * @return array<string, array<string|object>> Validation rules (as array) per attribute name
     */
    public static function getValidationRules($attributes = []): array
    {
        return static::getAttributePresetCollection()->getValidationRules($attributes);
    }

    /**
     * @param string|array<string> $attributes One or several attribute names to restrict the results to
     *
     * @return array<string, null|string> Validation message per attribute name
     */
    public static function getValidationMessages($attributes = []): array
    {
        return static::getAttributePresetCollection()->getValidationMessages($attributes);
    }

    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     * @param string|array<string> $attributes One or several attribute names to restrict the results to
     */
    public static function addColumnsToTable(Blueprint $table, $attributes = []): void
    {
        static::getAttributePresetCollection()->addColumnsToTable($table, $attributes);
    }

    /**
     * @param string|array<string> $attributes One or several attribute names to restrict the results to
     *
     * @return array<string, array<string|object>> Validation rules (as array) per attribute name
     */
    public static function getNovaFields($attributes = []): array
    {
        return static::getAttributePresetCollection()->getNovaFields($attributes);
    }
}

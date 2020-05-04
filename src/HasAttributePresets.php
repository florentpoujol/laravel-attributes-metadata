<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets;

use Illuminate\Database\Schema\Blueprint;

/**
 * @method static getRawAttributePresets(): array Shall be implemented typically in the model class itself
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
        return static::getAttributePresetCollection()
            ->filterByNames($attributes)
            ->mapWithKeys(function (BasePreset $attr, string $attrName) {
                return [$attrName, $attr->getValidationRules()];
            })
            ->toArray();
    }

    /**
     * @param string|array<string> $attributes One or several attribute names to restrict the results to
     *
     * @return array<string, array<string|object>> Validation rules (as array) per attribute name
     */
    public static function addColumnsToTable(Blueprint $table, $attributes = []): void
    {
        static::getAttributePresetCollection()
            ->filterByNames($attributes)
            ->each(function (BasePreset $attr) use ($table) {
                $attr
                    ->getColumnDefinitions()
                    ->addToTable($table);
            });
    }
}

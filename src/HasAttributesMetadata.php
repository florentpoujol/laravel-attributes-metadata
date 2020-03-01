<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

/**
 * @method static getAttributesMetadata(): array Shall be implemented typically in the model class itself
 */
trait HasAttributesMetadata
{
    /** @var \FlorentPoujol\LaravelModelMetadata\ModelMetadata */
    protected static $modelMetadata;

    /**
     * @return \FlorentPoujol\LaravelModelMetadata\ModelMetadata
     */
    public static function getMetadata(): ModelMetadata
    {
        if (static::$modelMetadata !== null) {
            return static::$modelMetadata;
        }

        $modelMetadataFqcn = ModelMetadata::class;
        if (property_exists(static::class, 'modelMetadataFqcn')) {
            $modelMetadataFqcn = static::$modelMetadataFqcn;
        }

        static::$modelMetadata = new $modelMetadataFqcn(
            static::class,
            static::getAttributesMetadata()
        );

        return static::$modelMetadata;
    }

    public static function getAttributeMetadata(string $name): ?AttributeMetadata
    {
        $modelMetadata = static::getMetadata();

        if ($modelMetadata->hasAttribute($name)) {
            return $modelMetadata->getAttributeMetadata($name);
        }

        return null;
    }

    /**
     * @param string|array<string> $attributes One or several attribute names to restrict the results to
     *
     * @return array<string, array<string|object>> Validation rules (as array) per attribute name
     */
    public static function getValidationRules($attributes = []): array
    {
        if (!is_array($attributes)) {
            $attributes = [$attributes];
        }

        return static::getMetadata()->getValidationRules($attributes);
    }

    /**
     * @param string|array<string> $attributes One or several attribute names to restrict the results to
     *
     * @return array<string, string> Validation messages per attribute name
     */
    public static function getValidationMessages($attributes = []): array
    {
        if (!is_array($attributes)) {
            $attributes = [$attributes];
        }

        return static::getMetadata()->getValidationMessages($attributes);
    }

    /**
     * @param string|array<string> $attributes One or several attribute names to restrict the results to
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public static function getNovaFields($attributes = []): array
    {
        if (!is_array($attributes)) {
            $attributes = [$attributes];
        }

        return static::getMetadata()->getNovaFields($attributes);
    }

}

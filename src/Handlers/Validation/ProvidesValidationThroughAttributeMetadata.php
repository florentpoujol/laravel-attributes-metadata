<?php


namespace FlorentPoujol\LaravelModelMetadata\Handlers\Validation;

use FlorentPoujol\LaravelModelMetadata\Handlers\Validation\Validation;

/**
 * @mixin \FlorentPoujol\LaravelModelMetadata\Traits\HasAttributesMetadata
 */
class ProvidesValidationThroughAttributeMetadata
{
    /** @var \FlorentPoujol\LaravelModelMetadata\Handlers\Validation\Validation */
    protected static $validationProvider;

    public static function getValidationProvider()
    {
        if (static::$validationProvider !== null) {
            return static::$validationProvider;
        }

        static::$validationProvider = new Validation(
            static::getAttributeMetadataCollection()
        );

        return static::$validationProvider;
    }

    /**
     * @param string|array<string> $attributes One or several attribute names to restrict the results to
     *
     * @return array<string, array<string|object>> Validation rules (as array) per attribute name
     */
    public static function getValidationRules($attributes = []): array
    {
        if (! is_array($attributes)) {
            $attributes = [$attributes];
        }

        return static::getValidationProvider()->getValidationRules($attributes);
    }

    /**
     * @param string|array<string> $attributes One or several attribute names to restrict the results to
     *
     * @return array<string, string> Validation messages per attribute name
     */
    public static function getValidationMessages($attributes = []): array
    {
        if (! is_array($attributes)) {
            $attributes = [$attributes];
        }

        return static::getValidationProvider()->getValidationMessages($attributes);
    }
}

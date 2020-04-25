<?php


namespace FlorentPoujol\LaravelModelMetadata\Validation;

use FlorentPoujol\LaravelModelMetadata\AttributeMetadata;

/**
 * @mixin \FlorentPoujol\LaravelModelMetadata\HasAttributesMetadata
 */
trait HasValidationConfig
{
    /**
     * @param string|array<string> $attributes One or several attribute names to restrict the results to
     *
     * @return array<string, array<string|object>> Validation rules (as array) per attribute name
     */
    public static function getValidationRules($attributes = []): array
    {
        return static::getAttributeConfigCollection()
            ->filterByNames($attributes)
            ->mapWithKeys(function (AttributeMetadata $attr) {
                return [
                    $attr->getName(),
                    $attr
                        ->getValidationHandler()
                        ->getRules()
                ];
            })
            ->toArray();
    }
}

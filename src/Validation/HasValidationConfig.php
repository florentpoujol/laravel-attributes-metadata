<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Validation;

use FlorentPoujol\LaravelAttributePresets\BasePreset;
use Illuminate\Validation\Factory;
use Illuminate\Validation\Validator;

/**
 * @mixin \FlorentPoujol\LaravelAttributePresets\HasAttributesMetadata
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
            ->mapWithKeys(function (BasePreset $attr) {
                return [
                    $attr->getName(),
                    $attr
                        ->getValidationHandler()
                        ->getRules()
                ];
            })
            ->toArray();
    }

    /**
     * @param array $data
     *
     * @return \Illuminate\Validation\Validator
     */
    public static function getValidator(array $data): Validator
    {
        $rules = self::getValidationRules(array_keys($data));

        /** @var \Illuminate\Validation\Factory $factory */
        $factory = app(Factory::class);

        return $factory->make($data, $rules);
    }
}

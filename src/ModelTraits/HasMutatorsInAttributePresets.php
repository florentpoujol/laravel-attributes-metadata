<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\ModelTraits;

/**
 * To be added on model classes that have mutators defined in their presets.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \FlorentPoujol\LaravelAttributePresets\ModelTraits\HasAttributePresets
 */
trait HasMutatorsInAttributePresets
{
    public function hasGetMutator(string $key): bool
    {
        if (parent::hasGetMutator($key)) {
            return true;
        }

        $preset = static::getAttributePreset($key);

        return $preset !== null && $preset->hasGetMutator();
    }

    protected function mutateAttribute(string $key, $value)
    {
        $preset = static::getAttributePreset($key);
        if ($preset === null) {
            return parent::mutateAttribute($key, $value);
        }

        return $preset->get($value);
    }

    public function hasSetMutator(string $key): bool
    {
        if (parent::hasSetMutator($key)) {
            return true;
        }

        $preset = static::getAttributePreset($key);

        return $preset !== null && $preset->hasSetMutator();
    }

    protected function setMutatedAttributeValue(string $key, $value)
    {
        $preset = static::getAttributePreset($key);
        if ($preset === null) {
            return parent::setMutatedAttributeValue($key, $value);
        }

        return $preset->set($value);
    }
}


<?php

namespace FlorentPoujol\LaravelModelMetadata;

/**
 * Main trait to be added on models
 */
trait HasAttributesMetadata
{
    /** @var ModelMetadata */
    protected static $modelMetadata;

    /**
     * @return array<string, array<int|string, mixed>>
     */
    public function getRawAttributesMetadata(): array
    {
        if (! property_exists(static::class, 'rawAttributesMetadata')) {
            static::$rawAttributesMetadata = [];
        }

        return static::$rawAttributesMetadata;
    }

    /**
     */
    public static function getMetadata()
    {
        if (static::$modelMetadata === null) {
            static::$modelMetadata = ModelMetadata::get(static::class);
        }

        return static::$modelMetadata;
    }

    // TODO add a shitload of convenience methods (maybe on their own trait)
}
<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

/**
 * Main trait to be added on models
 *
 * @property array<string, array<string|object>> $rawAttributesMetadata
 */
trait HasAttributesMetadata
{
    /** @var \FlorentPoujol\LaravelModelMetadata\ModelMetadata */
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
     * @return \FlorentPoujol\LaravelModelMetadata\ModelMetadata
     */
    public static function getMetadata(): ModelMetadata
    {
        if (static::$modelMetadata === null) {
            static::$modelMetadata = ModelMetadata::get(static::class);
        }

        return static::$modelMetadata;
    }
}

<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

/**
 */
trait HasAttributesMetadata
{
    public static function bootHasAttributesMetadata(): void
    {
        if (! property_exists(static::class, 'modelMetadataFqcn')) {
            static::$modelMetadataFqcn = '{bse class}';
        }

        static::$modelMetadata = static::$modelMetadataFqcn(
            static::getAttributesMetadata()
        );
    }


    /** @var \FlorentPoujol\LaravelModelMetadata\ModelMetadata */
    protected static $modelMetadata;

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

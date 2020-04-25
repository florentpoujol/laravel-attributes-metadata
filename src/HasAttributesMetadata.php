<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

/**
 * @method static getAttributesMetadata(): array Shall be implemented typically in the model class itself
 */
trait HasAttributesMetadata
{
    /** @var \FlorentPoujol\LaravelModelMetadata\AttributeConfigCollection */
    protected static $modelMetadata;

    /**
     * @return \FlorentPoujol\LaravelModelMetadata\AttributeConfigCollection
     */
    public static function getAttributeConfigCollection(): AttributeConfigCollection
    {
        if (static::$modelMetadata !== null) {
            return static::$modelMetadata;
        }

        $modelMetadataFqcn = AttributeConfigCollection::class;
        if (property_exists(static::class, 'modelMetadataFqcn')) {
            /** @noinspection PhpUndefinedFieldInspection */
            $modelMetadataFqcn = static::$modelMetadataFqcn;
        }

        static::$modelMetadata = new $modelMetadataFqcn(
            static::class, // model Fqcn
            static::getAttributesMetadata() // attribute metadata definitions
        );

        return static::$modelMetadata;
    }

    public static function getAttributeMetadata(string $name): ?AttributeMetadata
    {
        $modelMetadata = static::getAttributeConfigCollection();

        if ($modelMetadata->hasAttribute($name)) {
            return $modelMetadata->getAttributeMetadata($name);
        }

        return null;
    }
}

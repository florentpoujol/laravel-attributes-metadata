<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata\Traits;

use FlorentPoujol\LaravelModelMetadata\AttributeMetadataCollection;

/**
 * @method static getRawAttributeMetadata(): array Shall be implemented typically in the model class itself
 */
trait HasAttributesMetadata
{
    /** @var \FlorentPoujol\LaravelModelMetadata\AttributeMetadataCollection */
    protected static $attributeMetadataCollection;

    /**
     * @return \FlorentPoujol\LaravelModelMetadata\AttributeMetadataCollection
     */
    public static function getAttributeMetadataCollection(): AttributeMetadataCollection
    {
        if (static::$attributeMetadataCollection !== null) {
            return static::$attributeMetadataCollection;
        }

        static::$attributeMetadataCollection = new AttributeMetadataCollection(
            static::class, // model Fqcn
            static::getRawAttributeMetadata() // attribute metadata definitions
        );

        return static::$attributeMetadataCollection;
    }
}

<?php

namespace FlorentPoujol\LaravelModelMetadata\Handlers;

use FlorentPoujol\LaravelModelMetadata\AttributeMetadataCollection;

class BaseProvider
{
    /** @var \FlorentPoujol\LaravelModelMetadata\AttributeMetadataCollection */
    protected $attributeMetadataCollection;

    /**
     * @param \FlorentPoujol\LaravelModelMetadata\AttributeMetadataCollection $attributeMetadataCollection
     */
    public function __construct(AttributeMetadataCollection $attributeMetadataCollection)
    {
        $this->attributeMetadataCollection = $attributeMetadataCollection;
    }
}

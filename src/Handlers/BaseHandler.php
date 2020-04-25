<?php

namespace FlorentPoujol\LaravelAttributeMetadata\Handlers;

use FlorentPoujol\LaravelAttributeMetadata\AttributeMetadataCollection;

class BaseHandler
{
    /** @var \FlorentPoujol\LaravelAttributeMetadata\AttributeMetadataCollection */
    protected $attributeMetadataCollection;

    /**
     * @param \FlorentPoujol\LaravelAttributeMetadata\AttributeMetadataCollection $attributeMetadataCollection
     */
    public function __construct(AttributeMetadataCollection $attributeMetadataCollection)
    {
        $this->attributeMetadataCollection = $attributeMetadataCollection;
    }
}

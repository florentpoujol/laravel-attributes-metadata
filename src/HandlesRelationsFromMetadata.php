<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

use Illuminate\Support\Facades\Request;

/**
 * To be added on model classes that have relations defined in their metadata.
 *
 * Expect two properties to be set on the class
 * - `metadataModelFqcn` with the FQCN of the validated model
 * - `validatedAttributes` (optional) with the list of attributes to validates
 *
 * @property string $metadataModelFqcn
 * @property string[] $validatedAttributes
 */
trait HandlesRelationsFromMetadata
{
    /**
     * Dynamically retrieve attributes on the model.
     *
     * @param  string  $key
     * @return mixed
     */
    public function __get($key)
    {
        /** @var \FlorentPoujol\LaravelModelMetadata\ModelMetadata $modelMetadata */
        $modelMetadata = static::getMetadata();

        if (! $modelMetadata->hasAttribute($key)) {
            return parent::__get($key);
        }

        // this property exists in metadata
        $attrMeta = $modelMetadata->getAttributeMetadata($key);
        if (! $attrMeta->isRelation()) {
            return parent::__get($key);
        }


        return parent::__get($key);
    }
}


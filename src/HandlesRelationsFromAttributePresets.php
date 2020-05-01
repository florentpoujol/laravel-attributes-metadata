<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets;

/**
 * To be added on model classes that have relations defined in their presets.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \FlorentPoujol\LaravelAttributePresets\HasAttributesMetadata
 */
trait HandlesRelationsFromAttributePresets
{
    /**
     * @return null|\Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection
     */
    public function getRelationValue(string $key)
    {
        // overridden from the \Illuminate\Database\Eloquent\Concerns\HasAttributes trait

        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }

        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }

        // the current method is called when we are getting an attribute that has no values
        // in the 'attributes' array and has no getter, so we assume it may be a relation
        // but since the method doesn't actually exists we need this additional check
        $attrMeta = static::getAttributeMetadata($key);
        if (
            $attrMeta !== null &&
            $attrMeta->isRelation() &&
            $attrMeta->getRelation()['method'] === $key
        ) {
            // will call the relation method which will be catched by __call() below
            return $this->getRelationshipFromMethod($key);
        }

        return null;
    }

    public function __call(string $method, array $arguments)
    {
        $attrMeta = static::getAttributeMetadata($method);
        if ($attrMeta !== null && $attrMeta->isRelation()) {
            $relation = $attrMeta->getRelation();

            return $this->$relation['method'](...$relation['parameters']);
        }

        return parent::__call($method, $arguments);
    }
}


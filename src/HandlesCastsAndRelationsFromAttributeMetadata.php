<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

/**
 * To be added on model classes that have casts or relations defined in their metadata.
 *
 * @method static getMetadata(): \FlorentPoujol\LaravelModelMetadata\ModelMetadata
 */
trait HandlesCastsAndRelationsFromAttributeMetadata
{
    /**
     * @return mixed
     */
    public function getAttributeValue(string $key) // overriden from the HasAttribute trait
    {
        $attrMeta = static::getAttributeMetadata($key);
        if ($attrMeta === null) {
            return parent::getAttributeValue($key);
        }

        // this property exists in metadata
        $value = $this->getAttributeFromArray($key);

        if ($attrMeta->hasCastTarget()) {
            $target = $attrMeta->getCastTarget();
            array_unshift($target['parameters'], $value);

            return $this->$target['method'](...$target['parameters']);
        }

        return parent::getAttributeValue($key);
    }

    // overridden from the HasAttribute trait
    public function getRelationValue(string $key, AttributeMetadata $attrMeta = null)
    {
        if ($this->relationLoaded($key)) {
            return $this->relations[$key];
        }

        if (method_exists($this, $key)) {
            return $this->getRelationshipFromMethod($key);
        }

        // below is the part added from the built-in trait
        if (
            $attrMeta = static::getAttributeMetadata($key) !== null &&
            $attrMeta->isRelation() &&
            $attrMeta->getRelation()['method'] === $key
        ) {
            return $this->getRelationshipFromMethod($key);
        }

        return null;
    }

    /**
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        /** @var \FlorentPoujol\LaravelModelMetadata\ModelMetadata $modelMetadata */
        $modelMetadata = static::getMetadata();

        if (! $modelMetadata->hasAttribute($method)) {
            return parent::__call($method, $arguments);
        }

        $attrMeta = $modelMetadata->getAttributeMetadata($method);
        if ($attrMeta->isRelation()) {
            $relation = $attrMeta->getRelation();

            return $this->$relation['method'](...$relation['parameters']);
        }

        return parent::__call($method, $arguments);
    }
}


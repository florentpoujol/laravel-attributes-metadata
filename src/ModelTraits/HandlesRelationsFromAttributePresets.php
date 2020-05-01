<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\ModelTraits;

/**
 * To be added on model classes that have relations defined in their presets.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 * @mixin \FlorentPoujol\LaravelAttributePresets\HasAttributePresets
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
        $preset = static::getAttributePreset($key);
        if (
            $preset !== null &&
            $preset->isRelation() &&
            $preset->getRelation()['method'] === $key
        ) {
            // will call the relation method which will be catched by __call() below
            return $this->getRelationshipFromMethod($key);
        }

        return null;
    }

    public function __call(string $method, array $arguments)
    {
        $preset = static::getAttributePreset($method);
        if ($preset !== null && $preset->isRelation()) {
            $relation = $preset->getRelation();

            // $relation['method'] is 'belongsTo', 'hasMany', etc...
            return $this->$relation['method'](...$relation['parameters']);
        }

        return parent::__call($method, $arguments);
    }
}


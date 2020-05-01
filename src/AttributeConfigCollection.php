<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets;

use Illuminate\Support\Collection;

/**
 * This class is essentially a proxy for the collection of the model's attribute metadata collection.
 */
class AttributeConfigCollection extends Collection // attribute config collection
{
    /** @var string */
    protected $modelFqcn;

    public function __construct(string $modelFqcn, array $rawAttributesMetadata)
    {
        parent::__construct();

        $this->modelFqcn = $modelFqcn;
        $this->rawAttrsMetadata = $rawAttributesMetadata;

        $this->attrNames = array_keys($this->rawAttrsMetadata);
        $this->attrCollection = new Collection();
    }

    /**
     * Keeps the items for which the callback returns true.
     *
     * @param callable $callback
     *
     * @return static
     */
    public function keep(callable $callback)
    {
        return $this->filter($callback);
    }

    /** @var array<string, string|callable|\FlorentPoujol\LaravelAttributePresets\AttributeMetadata> */
    protected $rawAttrsMetadata;

    public function hasAttribute(string $name): bool
    {
        return isset($this->rawAttrsMetadata[$name]);
    }

    /** @var string[] List of the model's attributes (that have metadata) */
    protected $attrNames;

    /**
     * @return array<string>
     */
    public function getAttributeNames(): array
    {
        return $this->attrNames;
    }

    public function filterByNames(array $attributes = [])
    {
        if (! is_array($attributes)) {
            $attributes = [$attributes];
        }

        if (empty($attributes)) {
            return $this;
        }

        return $this->filter(function (AttributeMetadata $metadata) use ($attributes) {
            return in_array($metadata->getName(), $attributes);
        });
    }

    /** @var \Illuminate\Support\Collection&array<string, \FlorentPoujol\LaravelAttributePresets\AttributeMetadata> */
    protected $attrCollection;

    /**
     * @return \FlorentPoujol\LaravelAttributePresets\AttributeMetadata
     *
     * @throws \LogicException When the attribute has no metadata
     */
    public function getAttributeMetadata(string $name): AttributeMetadata
    {
        if ($this->attrCollection->has($name)) {
            return $this->attrCollection->get($name);
        }

        if (! in_array($name, $this->attrNames)) {
            throw new \LogicException(
                "Attribute '{$this->modelFqcn}->$name' doesn't have metadata"
            );
        }

        $object = $this->rawAttrsMetadata[$name];

        if (is_callable($object)) {
            $object = $object();
        } elseif (is_string($object)) { // Fqcn
            $object = new $object();
        }

        /** @var \FlorentPoujol\LaravelAttributePresets\AttributeMetadata $object */
        $object->setName($name);
        $this->attrCollection->put($name, $object);

        return $object;
    }

    /**
     * Return all or a subset of the attributes metadata collection
     *
     * @param null|array<string> $names
     *
     * @return \Illuminate\Support\Collection&array<string, \FlorentPoujol\LaravelAttributePresets\AttributeMetadata>
     */
    public function getAttrCollection(array $names = null)
    {
        $names = empty($names) ? $this->attrNames : $names;

        $collection = new Collection();
        foreach ($names as $name) {
            $collection->put($name, $this->getAttributeMetadata($name));
            // done like that instead of using the collection's only() method
            // so that metadata classes are created if they don't exists yet
        }

        return $collection;
    }

    /**
     * @return array<string, mixed> The attributes that have a default values and their values
     */
    public function getDefaultValues(): array
    {
        return $this
            ->getAttrCollection()
            ->filter(function (AttributeMetadata $meta) {
                return $meta->hasDefaultValue();
            })
            ->mapWithKeys(function (AttributeMetadata $meta, string $key) {
                return [$key => $meta->getDefaultValue()];
            })
            ->toArray();
    }

    /**
     * @return array<string> The fillable attributes
     */
    public function getFillable(): array
    {
        return $this
            ->getAttrCollection()
            ->filter(function (AttributeMetadata $meta) {
                return $meta->isFillable();
            })
            ->keys()
            ->toArray();
    }

    /**
     * @return array<string> The guarded attributes
     */
    public function getGuarded(): array
    {
        return $this
            ->getAttrCollection()
            ->filter(function (AttributeMetadata $meta) {
                return $meta->isGuarded();
            })
            ->keys()
            ->toArray();
    }

    /**
     * @return array<string> The hidden attributes
     */
    public function getHidden(): array
    {
        return $this
            ->getAttrCollection()
            ->filter(function (AttributeMetadata $meta) {
                return $meta->isHidden();
            })
            ->keys()
            ->toArray();
    }

    /**
     * @return array<string> The dates attributes
     */
    public function getDates(): array
    {
        return $this
            ->getAttrCollection()
            ->filter(function (AttributeMetadata $meta) {
                return $meta->isDate();
            })
            ->keys()
            ->toArray();
    }

    public function getPrimaryKeyMeta(): ?AttributeMetadata
    {
        return $this
            ->getAttrCollection()
            ->first(function (AttributeMetadata $meta) {
                return $meta->isPrimaryKey();
            });
    }
}

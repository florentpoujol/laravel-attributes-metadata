<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets;

use Illuminate\Support\Collection;

/**
 * This class is essentially a proxy for the collection of the model's attribute presets collection.
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

    /** @var array<string, string|callable|\FlorentPoujol\LaravelAttributePresets\BasePreset> */
    protected $rawAttrsMetadata;

    public function hasAttribute(string $name): bool
    {
        return isset($this->rawAttrsMetadata[$name]);
    }

    /** @var string[] List of the model's attributes (that have presets) */
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

        return $this->filter(function (BasePreset $preset) use ($attributes) {
            return in_array($preset->getName(), $attributes);
        });
    }

    /** @var \Illuminate\Support\Collection&array<string, \FlorentPoujol\LaravelAttributePresets\BasePreset> */
    protected $attrCollection;

    /**
     * @return \FlorentPoujol\LaravelAttributePresets\BasePreset
     *
     * @throws \LogicException When the attribute has no preset
     */
    public function getAttributeMetadata(string $name): BasePreset
    {
        if ($this->attrCollection->has($name)) {
            return $this->attrCollection->get($name);
        }

        if (! in_array($name, $this->attrNames)) {
            throw new \LogicException(
                "Attribute '{$this->modelFqcn}->$name' doesn't have preset"
            );
        }

        $object = $this->rawAttrsMetadata[$name];

        if (is_callable($object)) {
            $object = $object();
        } elseif (is_string($object)) { // Fqcn
            $object = new $object();
        }

        /** @var \FlorentPoujol\LaravelAttributePresets\BasePreset $object */
        $object->setName($name);
        $this->attrCollection->put($name, $object);

        return $object;
    }

    /**
     * Return all or a subset of the attributes preset collection
     *
     * @param null|array<string> $names
     *
     * @return \Illuminate\Support\Collection&array<string, \FlorentPoujol\LaravelAttributePresets\BasePreset>
     */
    public function getAttrCollection(array $names = null)
    {
        $names = empty($names) ? $this->attrNames : $names;

        $collection = new Collection();
        foreach ($names as $name) {
            $collection->put($name, $this->getAttributeMetadata($name));
            // done like that instead of using the collection's only() method
            // so that preset classes are created if they don't exists yet
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
            ->filter(function (BasePreset $meta) {
                return $meta->hasDefaultValue();
            })
            ->mapWithKeys(function (BasePreset $meta, string $key) {
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
            ->filter(function (BasePreset $meta) {
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
            ->filter(function (BasePreset $meta) {
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
            ->filter(function (BasePreset $meta) {
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
            ->filter(function (BasePreset $meta) {
                return $meta->isDate();
            })
            ->keys()
            ->toArray();
    }

    public function getPrimaryKeyMeta(): ?BasePreset
    {
        return $this
            ->getAttrCollection()
            ->first(function (BasePreset $meta) {
                return $meta->isPrimaryKey();
            });
    }
}

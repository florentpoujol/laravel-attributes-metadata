<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

use Illuminate\Support\Collection;

/**
 * This class is essentially a proxy for the collection of the model's attribute metadata collection.
 */
class AttributeMetadataCollection extends Collection
{
    /** @var array<string, \FlorentPoujol\LaravelModelMetadata\AttributeMetadata>  */
    protected $items = [];

    /** @var array<string, string|callable|\FlorentPoujol\LaravelModelMetadata\AttributeMetadata> */
    protected $rawAttrMetaArray;

    /** @var string */
    protected $modelFqcn;

    public function __construct(string $modelFqcn, array $rawAttributeMetadata)
    {
        parent::__construct();

        $this->modelFqcn = $modelFqcn;
        $this->rawAttrMetaArray = $rawAttributeMetadata;
    }

    /**
     * @return array<string>
     */
    public function getNames(): array
    {
        return array_keys($this->rawAttrMetaArray);
    }

    /**
     * @return null|\FlorentPoujol\LaravelModelMetadata\AttributeMetadata
     */
    public function get($name, $default = null): ?AttributeMetadata
    {
        if ($this->has($name)) {
            return $this->items[$name];
        }

        if (! isset($this->rawAttrMetaArray[$name])) {
            return $default;
        }

        $object = $this->rawAttrMetaArray[$name];
        if (is_callable($object)) {
            $object = $object();
        } elseif (is_string($object)) { // Fqcn
            $object = new $object();
        }

        /** @var \FlorentPoujol\LaravelModelMetadata\AttributeMetadata $object */
        $object->setName($name);

        $this->put($name, $object);

        return $object;
    }

    /**
     * @return array<string, \FlorentPoujol\LaravelModelMetadata\AttributeMetadata>
     */
    public function all(): array
    {
        $names = $this->getNames();
        foreach ($names as $name) {
            $this->get($name); // resolve all meta objects that aren't already
        }

        return $this->items;
    }
}

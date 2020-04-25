<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

use Illuminate\Support\Collection;

class AttributeMetadataCollection extends Collection
{
    /** @var array<string, \FlorentPoujol\LaravelModelMetadata\AttributeMetadata> Keys are the attribute names */
    protected $items = [];

    /** @var array<string, string|callable|\FlorentPoujol\LaravelModelMetadata\AttributeMetadata> */
    protected $rawAttrMetaArray;

    /** @var string */
    protected $modelFqcn;

    /**
     * @param array<string, string|callable|\FlorentPoujol\LaravelModelMetadata\AttributeMetadata> $rawAttributeMetadata
     */
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
     * Returns an AttributeMetadata instance if one exists for the attribute.
     *
     * @param null $default Has no effect.
     *
     * @return null|\FlorentPoujol\LaravelModelMetadata\AttributeMetadata
     */
    public function get($name, $default = null): ?AttributeMetadata
    {
        if ($this->has($name)) {
            return $this->items[$name];
        }

        if (! isset($this->rawAttrMetaArray[$name])) {
            return null;
        }

        $object = $this->rawAttrMetaArray[$name];
        if (is_callable($object)) {
            $object = $object();
        } elseif (is_string($object)) { // Fqcn
            $object = new $object();
        } elseif (is_array($object)) {
            $object = new AttributeMetadata($object);
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

<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;

class ModelMetadata
{
    /** @var string */
    protected $modelFqcn;

    public function __construct(string $modelFqcn)
    {
        $this->modelFqcn = $modelFqcn;
        $this->attrMetadataObjects = new Collection();

        if ($this->rawAttrsMetadata === null) {
            if (! property_exists($modelFqcn, 'attributesMetadata')) {
                throw new \LogicException(
                    "Missing public static property 'attributesMetadata' on model '$modelFqcn'."
                );
            }

            $this->rawAttrsMetadata = $modelFqcn::getRawAttributesMetadata();
            $this->attrNames = array_keys($this->rawAttrsMetadata);
        }
    }

    /** @var array<string, array<string|object>> */
    protected $rawAttrsMetadata;

    /** @var string[] List of the model's attributes (that have metadata) */
    protected $attrNames;

    /** @var array<string, \FlorentPoujol\LaravelModelMetadata\AttributeMetadata> */
    protected $attrMetadataObjects;

    /**
     * @return \FlorentPoujol\LaravelModelMetadata\AttributeMetadata
     */
    public function getAttributeMetadata(string $name): AttributeMetadata
    {
        if (! in_array($name, $this->attrNames)) {
            throw new \LogicException(
                "Attribute '{$this->modelFqcn}->$name' doesn't have metadata"
            );
        }

        if (! isset($this->attrMetadataObjects[$name])) {
            // it is time to instantiate an AttributeMetadata class for the attribute
            // TODO resolve custom objects
            $this->attrMetadataObjects[$name] =
                new AttributeMetadata($this->rawAttrsMetadata[$name]);
        }

        return $this->attrMetadataObjects[$name];
    }

    /**
     * @param array|string ...$attrNames One or several attribute name, or one array of attribute names
     *
     * @return array<string, \FlorentPoujol\LaravelModelMetadata\AttributeMetadata>
     */
    public function getAttrMetadataObjects(...$attrNames)
    {
        if (func_num_args() > 1) {
            $attrNames = func_get_args();
        } elseif (is_string($attrNames)) {
            $attrNames = [$attrNames];
        }

        $attrNames = array_intersect($this->attrNames, $attrNames); // remove non-existent attributes
        if (empty($attrNames)) {
            $attrNames = $this->attrNames;
        }

        foreach ($attrNames as $name) {
            if (! isset($this->attrMetadataObjects[$name])) {
                $this->getAttributeMetadata($name); // create if not exists
            }
        }

        return array_intersect_key($this->attrMetadataObjects, $attrNames);
    }

    /**
     * @return string[]
     */
    public function getAttributeNames(): array
    {
        return $this->attrNames;
    }

    public function hasAttribute(string $name): bool
    {
        return isset($this->rawAttrsMetadata[$name]);
    }

    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     */
    public function addColumnDefinitions(Blueprint $table): void
    {
        foreach ($this->attrMetadataObjects as $name => $metaObject) {
            $metaObject->addColumnDefinition($table);
        }
    }

    /**
     * @param array|string ...$attributes One or several attribute name, or one array of attribute names
     *
     * @return array<string, array<string|object>>
     */
    public function getCreateValidationRules(...$attributes): array
    {
        $attrMeta = $this->getAttrMetadataObjects(func_get_args());

        $rules = [];
        foreach ($attrMeta as $name => $metaObject) {
            $rules[$name] = $metaObject->getCreateValidationRules();
        }

        return $rules;
    }

    // getcastedattributes getcasts
    // getrelationattributes
    // getattributesdefaultvalues


    // --------------------------------------------------
    // static

    /** @var array<string, \FlorentPoujol\LaravelModelMetadata\ModelMetadata>  */
    protected static $modelMetadataPerFqcn = [];

    public static function get(string $modelFqcn)
    {
        if (! isset(static::$modelMetadataPerFqcn[$modelFqcn])) {
            static::$modelMetadataPerFqcn[$modelFqcn] =
                // TODO dynamically find the model metadata class
                new ModelMetadata($modelFqcn);
        }

        return static::$modelMetadataPerFqcn[$modelFqcn];
    }
}

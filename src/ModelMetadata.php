<?php

namespace FlorentPoujol\LaravelModelMetadata;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;

class ModelMetadata
{
    /** @var string */
    protected $modelFqcn;

    public function __construct(string $modelFqcn)
    {
        $this->modelFqcn = $modelFqcn;
        $this->attributesMetadata = new Collection();

        if ($this->rawAttributesMetadata === null) {
            if (! property_exists($modelFqcn, 'attributesMetadata')) {
                throw new \LogicException(
                    "Missing public static property 'attributesMetadata' on model '$modelFqcn'."
                );
            }

            $this->rawAttributesMetadata = (new $modelFqcn)->getRawAttributesMetadata();
            $this->attributes = array_keys($this->rawAttributesMetadata);
        }
    }

    /** @var array<string, array<string|object>> */
    protected $rawAttributesMetadata;

    /** @var string[] List of the model's attributes (that have metadata) */
    protected $attributes;

    /** @var array<string, AttributeMetadata> */
    protected $attributesMetadata;

    /**
     * @param string $attributeName Attribute
     *
     * @return AttributeMetadata
     */
    public function getAttributeMetadata(string $attributeName): AttributeMetadata
    {
        if (! in_array($attributeName, $this->attributes)) {
            throw new \LogicException(
                "Attribute '{$this->modelFqcn}->$attributeName' doesn't have metadata"
            );
        }

        if (! isset($this->attributesMetadata[$attributeName])) {
            // it is time to instantiate an AttributeMetadata class for the attribute
            // TODO resolve custom objects
            $this->attributesMetadata[$attributeName] =
                new AttributeMetadata($this->rawAttributesMetadata[$attributeName]);
        }

        return $this->attributesMetadata[$attributeName];
    }

    /**
     * @param array|string ...$wantedAttributes One or several attribute name, or one array of attribute names
     *
     * @return array<string, AttributeMetadata>
     */
    public function getAttributesMetadata(...$wantedAttributes)
    {
        if (func_num_args() > 1) {
            $wantedAttributes = func_get_args();
        } elseif (is_string($wantedAttributes)) {
            $wantedAttributes = [$wantedAttributes];
        }

        $wantedAttributes = array_intersect($this->attributes, $wantedAttributes); // remove non-existant attributes
        if (empty($wantedAttributes)) {
            $wantedAttributes = $this->attributes;
        }

        foreach ($wantedAttributes as $name) {
            if (!isset($this->attributesMetadata[$name])) {
                $this->getAttributeMetadata($name); // create if not exists
            }
        }

        return array_intersect_key($this->attributesMetadata, $wantedAttributes);
    }


    /**
     * @param Blueprint $table
     */
    public function getColumnDefinitions(Blueprint $table): void
    {
        /** @var AttributeMetadata $metaObject */
        foreach ($this->attributesMetadata as $name => $metaObject) {
            $metaObject->getColumnDefinition($table);
        }
    }

    /**
     * @param array|string ...$attributes One or several attribute name, or one array of attribute names
     *
     * @return array<string, array<string|object>>
     */
    public function getCreateValidationRules(...$attributes): array
    {
        $attrMeta = $this->getAttributesMetadata(func_get_args());

        $rules = [];
        /** @var AttributeMetadata $metaObject */
        foreach ($attrMeta as $name => $metaObject) {
            $rules[$name] = $metaObject->getCreateValidationRules();
        }

        return $rules;
    }


    // --------------------------------------------------
    // static

    protected static $modelMetadataPerFqcn = [];

    public static function get(string $modelFqcn)
    {
        if (isset(static::$modelMetadataPerFqcn[$modelFqcn])) {
            return static::$modelMetadataPerFqcn[$modelFqcn];
        }

        static::$modelMetadataPerFqcn[$modelFqcn] =
            // TODO dynamically find the model metadata class
            new ModelMetadata($modelFqcn);

        return static::$modelMetadataPerFqcn[$modelFqcn];
    }
}
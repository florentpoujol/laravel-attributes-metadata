<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection as BaseCollection;

class Collection extends BaseCollection
{
    /** @var array<string, \FlorentPoujol\LaravelAttributePresets\BasePreset> Keys are the attribute names */
    protected $items = [];

    /** @var array<string, string|callable|\FlorentPoujol\LaravelAttributePresets\BasePreset> */
    protected $rawPresets;

    /** @var string */
    protected $modelFqcn;

    /**
     * @param array<string, string|callable|\FlorentPoujol\LaravelAttributePresets\BasePreset> $presets
     */
    public function __construct(string $modelFqcn, array $presets)
    {
        parent::__construct();

        $this->modelFqcn = $modelFqcn;
        $this->rawPresets = $presets;
    }

    /**
     * Returns a preset instance if one exists for the attribute.
     *
     * @param null $default This parameter has no effect.
     *
     * @return null|\FlorentPoujol\LaravelAttributePresets\BasePreset
     */
    public function get($name, $default = null): ?BasePreset
    {
        if ($this->has($name)) {
            return $this->items[$name];
        }

        if (! isset($this->rawPresets[$name])) {
            return null;
        }

        $object = $this->rawPresets[$name];
        if (is_callable($object)) {
            $object = $object();
        } elseif (is_string($object)) { // Fqcn
            $object = new $object();
        } elseif (is_array($object)) {
            $object = new BasePreset($object);
        }

        /** @var \FlorentPoujol\LaravelAttributePresets\BasePreset $object */
        $object->setName($name);

        $this->put($name, $object);

        return $object;
    }

    /**
     * @return array<string, \FlorentPoujol\LaravelAttributePresets\BasePreset>
     */
    public function all(): array
    {
        $names = $this->getNames();
        foreach ($names as $name) {
            $this->get($name); // resolve all preset objects that aren't already
        }

        return $this->items;
    }

    /**
     * @return array<string>
     */
    public function getNames(): array
    {
        return array_keys($this->rawPresets);
    }

    /**
     * @param string|array<string> $attributes One or several attribute names to filter the collection with
     */
    public function filterByNames($attributes = []): self
    {
        if (! is_array($attributes)) {
            $attributes = [$attributes];
        }

        if (empty($attributes)) {
            return $this;
        }

        return $this->filter(function (BasePreset $attr) use ($attributes) {
            return in_array($attr->getName(), $attributes);
        });
    }

    /**
     * @return array<string, mixed> The attributes that have a default values and their values
     */
    public function getDefaultValues(): array
    {
        return $this
            ->keep(function (BasePreset $attr) {
                return $attr->hasDefaultValue();
            })
            ->mapWithKeys(function (BasePreset $attr, string $attrName) {
                return [$attrName => $attr->getDefaultValue()];
            })
            ->toArray();
    }

    /**
     * @return array<string> The fillable attributes
     */
    public function getFillable(): array
    {
        return $this
            ->keep(function (BasePreset $attr) {
                return $attr->isFillable();
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
            ->keep(function (BasePreset $attr) {
                return $attr->isGuarded();
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
            ->keep(function (BasePreset $attr) {
                return $attr->isHidden();
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
            ->keep(function (BasePreset $attr) {
                return $attr->isDate();
            })
            ->keys()
            ->toArray();
    }

    /**
     * @return array<string, mixed> The attributes that have a default values and their values
     */
    public function getCasts(): array
    {
        return $this
            ->keep(function (BasePreset $attr) {
                return $attr->hasCast();
            })
            ->mapWithKeys(function (BasePreset $attr, string $attrName) {
                return [$attrName => $attr->getCast()];
            })
            ->toArray();
    }

    public function getPrimaryKeyPreset(): ?BasePreset
    {
        return $this
            ->first(function (BasePreset $preset) {
                return $preset->isPrimaryKey();
            });
    }

    public function getValidationRules($attributes = []): array
    {
        return $this
            ->filterByNames($attributes)
            ->mapWithKeys(function (BasePreset $attr, string $attrName) {
                return [$attrName => $attr->getValidationRules()];
            })
            ->toArray();
    }

    public function getValidationMessages($attributes = []): array
    {
        return $this
            ->filterByNames($attributes)
            ->mapWithKeys(function (BasePreset $attr, string $attrName) {
                return [$attrName => $attr->getValidationMessage()];
            })
            ->toArray();
    }

    /**
     * @param string|array<string> $attributes One or several attribute names to restrict the results to
     *
     * @return array<string, array<string|object>> Validation rules (as array) per attribute name
     */
    public function addColumnsToTable(Blueprint $table, $attributes = []): void
    {
        $this
            ->filterByNames($attributes)
            ->each(function (BasePreset $attr) use ($table) {
                $attr->addToTable($table);
            });
    }

    public function getNovaIndexFields($attributes = [])
    {
        return $this
            ->filterByNames($attributes)
            ->keep(function (BasePreset $attr) {
                return $this->hasIndexNovaField();
            })
            ->mapWithKeys(function (BasePreset $attr, string $attrName) {
                return [$attrName => $attr->getNovaIndexField()];
            });
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
}

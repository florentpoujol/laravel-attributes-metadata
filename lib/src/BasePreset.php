<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets;

use FlorentPoujol\LaravelAttributePresets\Definitions\Fluent;
use FlorentPoujol\LaravelAttributePresets\PresetTraits\ProvidesColumnDefinitions;
use FlorentPoujol\LaravelAttributePresets\PresetTraits\ProvidesValidation;

class BasePreset extends Fluent implements Preset
{
    use ProvidesValidation;
    use ProvidesColumnDefinitions;

    // use ProvidesNovaFields;
    // use ProvidesModelMetadata;
    // use ProvidesRelations;
    // use Helpers;

    /** @var array<string, mixed> */
    protected static $baseDefinitions = [];

    /**
     * @return array<string, mixed>
     */
    public static function getBaseDefinitions(): array
    {
        return static::$baseDefinitions;
    }

    public function __construct(array $attributes = [])
    {
        $baseDefinitions = static::getBaseDefinitions();
        if (! empty($baseDefinitions)) {
            $this->fill($baseDefinitions);
        }

        parent::__construct($attributes);
    }

    /** @var null|string The name of the attribute. Usually set from ModelMetadata->getAttributeMetadata()`. */
    protected $name;

    /**
     * @return static
     */
    public function name(string $name)
    {
        return $this->setName($name);
    }

    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray()
    {
        $attributes = $this->attributes;
        $attributes['name'] = $this->getName();

        $attributes['dbColumn'] = $this->getColumnDefinitions()->toArray();
        $attributes['validation'] = $this->getValidationDefinitions()->toArray();
        // $attributes['novaField'] = $this->getNovaFieldDefinitions()->toArray();

        return $attributes;
    }

    public function hasGetMutator(): bool
    {
        return method_exists($this, 'get');
    }

    public function hasSetMutator(): bool
    {
        return method_exists($this, 'set');
    }
}

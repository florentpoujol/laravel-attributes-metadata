<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets;

use FlorentPoujol\LaravelAttributePresets\Definitions\Fluent;
use FlorentPoujol\LaravelAttributePresets\PresetTraits\Helpers;
use FlorentPoujol\LaravelAttributePresets\PresetTraits\ProvidesColumnDefinitions;
use FlorentPoujol\LaravelAttributePresets\PresetTraits\ProvidesModelMetadata;
use FlorentPoujol\LaravelAttributePresets\PresetTraits\ProvidesNovaFields;
use FlorentPoujol\LaravelAttributePresets\PresetTraits\ProvidesRelations;
use FlorentPoujol\LaravelAttributePresets\PresetTraits\ProvidesValidation;

class BasePreset extends Fluent implements Preset
{
    use ProvidesValidation;
    use ProvidesColumnDefinitions;
    use ProvidesNovaFields;
    use ProvidesModelMetadata;
    use ProvidesRelations;
    use Helpers;

    /** @var null|string The name of the attribute. Usually set from ModelMetadata->getAttributeMetadata()`. */
    protected $name;

    /**
     * @return static
     */
    public function name(string $name)
    {
        return $this->setName($name);
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function toArray()
    {
        $attributes = $this->attributes;
        $attributes['name'] = $this->getName();

        $attributes['dbColumn'] = $this->getColumnDefinitions()->toArray();
        $attributes['validation'] = $this->getValidationDefinitions()->toArray();
        $attributes['novaField'] = $this->getNovaFieldDefinitions()->toArray();

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

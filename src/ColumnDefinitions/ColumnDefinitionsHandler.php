<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\ColumnDefinitions;

use FlorentPoujol\LaravelAttributePresets\BasePreset;
use FlorentPoujol\LaravelAttributePresets\HasAttributeName;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

class ColumnDefinitionsHandler extends ColumnDefinition
{
    use HasAttributeName;

    public static function make(BasePreset $attr)
    {
        return (new static)->setAttributeName($attr->getName());
    }

    public function removeDefinition(string $key): self
    {
        $this->offsetUnset($key);

        return $this;
    }

    public function clear(): void
    {
        $this->type = null;
        $this->typeParams = [];
        $this->attributes = [];
    }

    /** @var string */
    protected $type;

    /** @var array<mixed> */
    protected $typeParams = [];

    /**
     * @param string $type
     */
    public function setType(string $type, ...$params): self
    {
        $this->type = $type;
        $this->typeParams = $params;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     */
    public function addToTable(Blueprint $table): void
    {
        if ($this->type === null) {
            return;
        }

        $table->addColumn($this->type, $this->getAttributeName(), $this->toArray());
    }
}

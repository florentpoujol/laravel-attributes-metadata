<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition as LaravelColumnDefinition;

class ColumnDefinition extends LaravelColumnDefinition
{
    /** @var null|string The name of the attribute. */
    public $attributeName;

    public static function make(string $attributeName)
    {
        $instance = new static();
        $instance->attributeName = $attributeName;

        return $instance;
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

    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     */
    public function addToTable(Blueprint $table): void
    {
        if ($this->type === null) {
            return;
        }

        $table->addColumn($this->type, $this->attributeName, $this->toArray());
    }
}

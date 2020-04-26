<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata\ColumnDefinitions;

use FlorentPoujol\LaravelModelMetadata\AttributeMetadata;
use FlorentPoujol\LaravelModelMetadata\HasAttributeName;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

class ColumnDefinitionsHandler extends ColumnDefinition
{
    use HasAttributeName;

    public static function make(AttributeMetadata $attr)
    {
        return (new static)->setAttributeName($attr->getName());
    }

    public function removeDefinition(string $key): self
    {
        $this->offsetUnset($key);

        return $this;
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

    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     */
    public function addToTable(Blueprint $table): void
    {
        $table->addColumn($this->type, $this->getAttributeName(), $this->toArray());
    }
}

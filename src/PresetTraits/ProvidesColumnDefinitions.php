<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\PresetTraits;

use FlorentPoujol\LaravelAttributePresets\ColumnDefinition;

trait ProvidesColumnDefinitions
{
    /** @var \FlorentPoujol\LaravelAttributePresets\ColumnDefinition */
    protected $columnDefinitions;

    /**
     * @return \FlorentPoujol\LaravelAttributePresets\ColumnDefinition
     */
    public function getColumnDefinitions(): ColumnDefinition
    {
        if ($this->columnDefinitions === null) {
            $this->columnDefinitions = ColumnDefinition::make($this->name);
        }

        return $this->columnDefinitions;
    }

    public function clearColumnDefinitions(): void
    {
        if ($this->columnDefinitions !== null) {
            $this->columnDefinitions->clear();
            $this->columnDefinitions = null;
        }
    }
}

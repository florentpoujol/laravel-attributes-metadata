<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\PresetTraits;

use FlorentPoujol\LaravelAttributePresets\Definitions\DbColumn;
use Illuminate\Database\Schema\Blueprint;

trait ProvidesColumnDefinitions
{
    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     *
     * @return static
     */
    public function addToTable(Blueprint $table)
    {
        if ($this->columnDefinitions !== null) {
            $this->columnDefinitions
                ->name($this->getName())
                ->addToTable($table);
        }

        return $this;
    }

    public function hasDbColumn(): bool
    {
        return
            $this->columnDefinitions !== null &&
            $this->columnDefinitions->has('type');
    }

    // --------------------------------------------------

    /** @var \FlorentPoujol\LaravelAttributePresets\Definitions\DbColumn */
    protected $columnDefinitions;

    /**
     * @param \FlorentPoujol\LaravelAttributePresets\Definitions\DbColumn $definitions
     *
     * @return static
     */
    public function setColumnDefinitions(DbColumn $definitions)
    {
        $this->columnDefinitions = $definitions;

        return $this;
    }

    /**
     * @return \FlorentPoujol\LaravelAttributePresets\Definitions\DbColumn
     */
    public function getColumnDefinitions(): DbColumn
    {
        if ($this->columnDefinitions === null) {
            $this->columnDefinitions = new DbColumn();
        }

        return $this->columnDefinitions;
    }

    /**
     * Catch the call for the 'dbColumn' key when the base preset itself is filled
     *
     * @param array|callable $attributesOrCallback Will fill or tap into the underlying definition instance
     *
     * @return static
     */
    public function dbColumn($attributesOrCallback)
    {
        $method = is_callable($attributesOrCallback) ? 'tap' : 'fill';

        $this->getColumnDefinitions()->$method($attributesOrCallback);

        return $this;
    }
}

<?php


namespace FlorentPoujol\LaravelModelMetadata\Traits;


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;

trait HasColumnDefinitions
{
    /**
     * Keys are method names of the \Illuminate\Database\Schema\ColumnDefinition class
     * Values are argument(s), if any, of these methods
     *
     * There is one special column definition key : 'type'
     * It itself has a value as array with 'method' and 'args' keys which match
     * the method (and arguments) to be called first on the \Illuminate\Database\Schema\Blueprint class
     *
     * @var array<string, null|mixed|array<mixed>>
     */
    protected $columnDefinitions = [];

    /**
     * @param string $type Must match one of the public methods of the Blueprint class
     * @param null|mixed $value one or several (as array) arguments for the type's method
     */
    public function setColumnType(string $type, $value = null)
    {
        $this->columnDefinitions['type'] = ['method' => $type, 'args' => $value];

        return $this;
    }

    public function getColumnType(): ?string
    {
        return $this->columnDefinitions['type']['method'] ?? null;
    }

    /**
     * @param null|mixed $value
     */
    public function addColumnDefinition(string $key, $value = null): self
    {
        $this->columnDefinitions[$key] = $value;

        return $this;
    }

    public function removeColumnDefinition(string $key): self
    {
        unset($this->columnDefinitions[$key]);

        return $this;
    }

    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     *
     * @return null|\Illuminate\Database\Schema\ColumnDefinition
     */
    public function addColumnToTable(Blueprint $table): ?ColumnDefinition
    {
        if (empty($this->columnDefinition)) {
            return null;
        }

        $type = $this->columnDefinitions['type'];
        $arguments = $type['args'] ?? [];
        if (! is_array($arguments)) {
            $arguments = [$arguments];
        }

        /** @var \Illuminate\Database\Schema\ColumnDefinition $columnDefinition */
        $columnDefinition = $table->$type['method'](...$arguments);

        foreach ($this->columnDefinitions as $methodName => $arguments) {
            if ($methodName === 'type') {
                continue;
            }

            if (is_int($methodName)) {
                $methodName = $arguments;
                $arguments = [];
            }

            if ($arguments === null) {
                $arguments = [];
            } elseif (! is_array($arguments)) {
                $arguments = [$arguments];
            }

            $columnDefinition->$methodName(...$arguments);
        }

        return $columnDefinition;
    }

    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     *
     * @return \Illuminate\Database\Schema\ColumnDefinition
     */
    public function updateColumnToTable(Blueprint $table): ColumnDefinition
    {
        $columnDefinition = $this->addColumnToTable($table);

        return $columnDefinition->change();
    }

    public function hasColumnInDB(): bool
    {
        return empty($this->columnDefinitions);
    }
}

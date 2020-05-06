<?php

namespace FlorentPoujol\LaravelAttributePresets\PresetTraits;

use FlorentPoujol\LaravelAttributePresets\NovaFieldDefinition;
use Laravel\Nova\Fields\Field;

trait ProvidesNovaFields
{
    protected $indexNovaDefinitions;
    protected $detailNovaDefinitions;
    protected $createNovaDefinitions;
    protected $updateNovaDefinitions;

    public function getIndexNovaDefinitions()
    {
        if ($this->indexNovaDefinitions === null) {
            $this->indexNovaDefinitions = new NovaFieldDefinition();
        }

        return $this->indexNovaDefinitions;
    }

    public function getUpdateNovaDefinitions()
    {
        return $this->updateNovaDefinitions ?: $this->getIndexNovaDefinitions();
    }

    public function setIndexNovaDefinitions($defs)
    {
        $this->indexNovaDefinitions = $defs;
    }


    /** @var \FlorentPoujol\LaravelAttributePresets\NovaFieldDefinition */
    protected $novaFieldDefinitions;

    /**
     * @return \FlorentPoujol\LaravelAttributePresets\NovaFieldDefinition
     */
    public function getNovaDefinitions(): NovaFieldDefinition
    {
        if ($this->novaFieldDefinitions === null) {
            $this->novaFieldDefinitions = new NovaFieldDefinition();
        }

        return $this->novaFieldDefinitions;
    }

    /** @var array<string, null|\Laravel\Nova\Fields\Field> */
    protected $novaFields = [
        'index' => null,
        'detail' => null,
        'create' => null,
        'update' => null,
    ];

    /** @var string */
    protected $novaFieldFqcn;

    /** @var array<string, null|mixed|array<mixed>>  */
    // protected $novaFieldDefinitions = [
    //     'sortable' => null
    // ];

    public function setNovaFieldType(string $typeOrFqcn): self
    {


        return $this;
    }

    /**
     * @param null|mixed $value
     */
    public function setNovaFieldDefinition(string $key, $value = null): self
    {
        $this->novaFieldDefinitions[$key] = $value;

        return $this;
    }

    public function removeNovaFieldDefinition(string $key): self
    {
        unset($this->novaFieldDefinitions[$key]);

        return $this;
    }

    /**
     * @param null|string $page 'index', 'details', 'create', 'update'
     *
     * @return array<\Laravel\Nova\Fields\Field>
     */
    public function getNovaFields(string $page = null): array
    {
        return
            $this->novaFields[$page ?: 'index'] ??
            $this->novaFields['index'] ?? [];
    }

    /** @var null|\Laravel\Nova\Fields\Field|\FlorentPoujol\LaravelAttributePresets\NovaFieldDefinition */
    protected $novaField;

    /**
     * @param null|\Laravel\Nova\Fields\Field|\FlorentPoujol\LaravelAttributePresets\NovaFieldDefinition $field
     */
    public function setNovaField($field): self
    {
        $this->novaField = $field;

        return $this;
    }

    /**
     * @return null|\Laravel\Nova\Fields\Field
     */
    public function getNovaField(): ?Field
    {
        if ($this->novaField instanceof NovaFieldDefinition) {
            $this->novaField = $this->novaField->getFieldInstance();
        }

        return $this->novaField;
    }

    /** @var null|\Laravel\Nova\Fields\Field|\FlorentPoujol\LaravelAttributePresets\NovaFieldDefinition */
    protected $novaCreateField;

    /**
     * @param null|\Laravel\Nova\Fields\Field|\FlorentPoujol\LaravelAttributePresets\NovaFieldDefinition $field
     */
    public function setNovaCreateField($field): self
    {
        $this->novaCreateField = $field;

        return $this;
    }

    public function getNovaCreateField(): ?Field
    {
        if ($this->novaCreateField !== null) {
            if ($this->novaCreateField instanceof NovaFieldDefinition) {
                $this->novaCreateField = $this->novaCreateField->getFieldInstance($this->name);
            }

            return $this->novaCreateField;
        }

        return $this->getNovaField();
    }
}

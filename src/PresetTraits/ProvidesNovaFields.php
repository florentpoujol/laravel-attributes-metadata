<?php

namespace FlorentPoujol\LaravelAttributePresets\PresetTraits;

use Laravel\Nova\Fields\Field;

trait ProvidesNovaFields
{
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
    protected $novaFieldDefinitions = [
        'sortable' => null
    ];

    public function setNovaFieldType(string $typeOrFqcn): self
    {
        $typeOrFqcn = ucfirst($typeOrFqcn);
        switch ($typeOrFqcn) {
            case 'Id':
                $typeOrFqcn = 'ID';
                break;
            case 'String':
                $typeOrFqcn = 'Text';
                break;
            case 'Text':
                $typeOrFqcn = 'Textarea';
                break;
            case 'Json':
                $typeOrFqcn = 'Code';
                break;
            case 'Datetime':
            case 'Timestamp':
                $typeOrFqcn = 'DateTime';
                break;
        }

        $this->novaFieldFqcn = '\\Laravel\\Nova\\Fields\\' . $typeOrFqcn;

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

    /**
     * @param mixed ...$args
     *
     * @return \Laravel\Nova\Fields\Field
     */
    public function setupNovaField(...$args): Field
    {

    }

    /**
     * @param null|\Laravel\Nova\Fields\Field $field
     * @param null|string $page 'index', 'details', 'create', 'update'
     */
    public function setNovaField($field, string $page = null): self
    {
        if ($page !== null) {
            $this->novaFields[$page] = $field;

            return $this;
        }

        if ($field === null) {
            $this->novaFields = [
                'index' => null,
                'details' => null,
                'create' => null,
                'update' => null,
            ];

            return $this;
        }

        // $field is an instance of Field and $page is null
        if ($field->showOnIndex) {
            $this->novaFields['index'] = $field;
        }
        if ($field->showOnDetail) {
            $this->novaFields['details'] = $field;
        }
        if ($field->showOnCreation) {
            $this->novaFields['create'] = $field;
        }
        if ($field->showOnUpdate) {
            $this->novaFields['update'] = $field;
        }

        return $this;
    }
}

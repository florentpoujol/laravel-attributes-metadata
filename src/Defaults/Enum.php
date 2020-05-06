<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\BasePreset;
use FlorentPoujol\LaravelAttributePresets\NovaFieldDefinition;

class Enum extends BasePreset
{
    /**
     * @param array<string> $allowedValues
     */
    public function __construct(array $allowedValues)
    {
        $this->setAllowedValues($allowedValues);
    }

    /** @var array<string> */
    protected $allowedValues = [];

    /**
     * @param array<string> $values
     */
    public function setAllowedValues(array $values): self
    {
        $this->allowedValues = $values;

        $this->getColumnDefinitions()->setType('enum', $values);

        $this->setValidationRule('in', $values);

        return $this;
    }

    /**
     * @param array<string, string> $options
     */
    public function setNovaSelectOptions(array $options, bool $displayUsingLabels = true): self
    {
        /** @var \Laravel\Nova\Fields\Select $novaField */
        $novaField = NovaFieldDefinition::select()
            ->sortable()
            ->options($options);

        if ($displayUsingLabels) {
            $novaField->displayUsingLabels();
        }

        $this->setNovaField($novaField);

        return $this;
    }
}

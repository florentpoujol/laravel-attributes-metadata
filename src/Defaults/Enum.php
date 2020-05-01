<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\AttributeMetadata;

class Enum extends AttributeMetadata
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

        $this->getValidationHandler()->setRule('in', $values);

        return $this;
    }

    /**
     * @param array<string, string> $options
     */
    public function setNovaSelectOptions(array $options, bool $displayUsingLabels = true): self
    {
        $this->setNovaFieldDefinition('options', $options);

        if ($displayUsingLabels) {
            $this->setNovaFieldDefinition('displayUsingLabels');
        }

        return $this;
    }
}

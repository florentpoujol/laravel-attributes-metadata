<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

class Enum extends AttributeMetadata
{
    /**
     * @param array<string> $allowedValues
     */
    public function __construct(array $allowedValues = null)
    {
        parent::__construct();

        if (!empty($allowedValues)) {
            $this->setAllowedValues($allowedValues);
        }

        $this
            ->setColumnType('enum', $this->allowedValues)
            ->setValidationRule('in', $this->allowedValues)
            ->setNovaFieldType('select');
    }

    /** @var array<string> */
    protected $allowedValues = [];

    /**
     * @param array<string> $values
     *
     * @return $this
     */
    public function setAllowedValues(array $values): self
    {
        $this->allowedValues = $values;

        $this->setValidationRule('in', $values);

        return $this;
    }

    /**
     * @param array<string, string> $options
     *
     * @return $this
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
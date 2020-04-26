<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata\Defaults;

use FlorentPoujol\LaravelModelMetadata\AttributeMetadata;

class Set extends AttributeMetadata
{
    /**
     * @param array<string> $allowedValues
     */
    public function __construct(array $allowedValues)
    {
        $this->setAllowedValues($allowedValues);
    }

    /** @var array<string>  */
    protected $allowedValues = [];

    /**
     * @param array<string> $values
     */
    public function setAllowedValues(array $values): self
    {
        $this->allowedValues = $values;

        $this->getColumnDefinitions()->setType('set', $values);

        $this->getValidationHandler()->setRule('in', $values);

        return $this;
    }
}

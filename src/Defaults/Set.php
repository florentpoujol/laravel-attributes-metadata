<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\BasePreset;

class Set extends BasePreset
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

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

        $this->dbColumn(['set' => $values]);
        $this->validation(['in' => $values]);

        // TODO: add a special getter/setter to work with Nova boolean group fields

        return $this;
    }
}

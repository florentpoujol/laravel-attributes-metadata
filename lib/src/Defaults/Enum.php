<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Defaults;

use FlorentPoujol\LaravelAttributePresets\BasePreset;

class Enum extends BasePreset
{
    protected static $baseDefinitions = [
        'dbColumn' => ['enum' => []],
        'novaField' => ['Select', 'sortable'],
    ];

    /**
     * @param array<string> $allowedValues
     */
    public function __construct(array $allowedValues)
    {
        parent::__construct();

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

        $this->dbColumn(['enum' => $values]);
        $this->validation(['in' => $values]);
        $this->novaField(['options' => array_combine($values, $values)]);

        return $this;
    }
}

<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\PresetTraits;

use FlorentPoujol\LaravelAttributePresets\Definitions\Validation;

trait ProvidesValidation
{
    /**
     * @return array<string|\Illuminate\Validation\Rule>
     */
    public function getValidationRules(): array
    {
        return $this->getValidationDefinitions()->getRules();
    }

    public function getValidationMessage(): ?string
    {
        return $this->getValidationDefinitions()->getMessage();
    }

    // --------------------------------------------------

    /** @var \FlorentPoujol\LaravelAttributePresets\Definitions\Validation */
    protected $validationDefinitions;

    /**
     * @param \FlorentPoujol\LaravelAttributePresets\Definitions\Validation $definitions
     *
     * @return static
     */
    public function setValidationDefinitions(Validation $definitions)
    {
        $this->validationDefinitions = $definitions;

        return $this;
    }

    /**
     * @return \FlorentPoujol\LaravelAttributePresets\Definitions\Validation
     */
    public function getValidationDefinitions(): Validation
    {
        if ($this->validationDefinitions === null) {
            $this->validationDefinitions = new Validation();
        }

        return $this->validationDefinitions;
    }

    /**
     * Catch the call for the 'validation' key when the base preset itself is filled
     *
     * @param array|callable $attributesOrCallback Will fill or tap into the underlying definition instance
     *
     * @return static
     */
    public function validation($attributesOrCallback)
    {
        $method = \is_callable($attributesOrCallback) ? 'tap' : 'fill';

        $this->getValidationDefinitions()->$method($attributesOrCallback);

        return $this;
    }
}

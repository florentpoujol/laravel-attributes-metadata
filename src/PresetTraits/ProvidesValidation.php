<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\PresetTraits;

trait ProvidesValidation
{
    /**
     * Keys are rule name, or Fqcn when they are objects.
     * Values are full rules (with "arguments" after the semicolon, if any) or instances.
     *
     * @var array<string, string|object>
     */
    protected $validationRules = [];

    /**
     * @return array<string|object>
     */
    public function getValidationRules(): array
    {
        return array_values($this->validationRules);
    }

    /**
     * @param array<string|object> $validationRules
     */
    public function setValidationRules(array $validationRules): self
    {
        $this->validationRules = [];

        foreach ($validationRules as $rule) {
            $this->setValidationRule($rule);
        }

        return $this;
    }

    /**
     * @param string|object $rule
     * @param null|mixed $value
     */
    public function setValidationRule($rule, $value = null): self
    {
        if ($value === null) {
            $value = $rule;
        }

        if (is_string($rule) && strpos($rule, ':') !== false) {
            // for the rules that takes "arguments" after a semicolon like 'exists', or 'in'
            $rule = explode(':', $rule, 2)[0]; // keep the name
        } elseif (is_object($rule)) {
            $rule = get_class($rule);
        }

        $this->validationRules[$rule] = $value;

        return $this;
    }

    /**
     * @param string|object $rule
     */
    public function removeValidationRule($rule): self
    {
        if (is_object($rule)) {
            $rule = get_class($rule);
        }

        unset($this->validationRules[$rule]);

        return $this;
    }
}

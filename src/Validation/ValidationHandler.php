<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata\Validation;

class ValidationHandler
{
    /**
     * Keys are rule name, or Fqcn when they are objects.
     * Values are full rules (with "arguments" after the semicolon, if any) or instances.
     *
     * @var array<string, string|object>
     */
    protected $rules = [];

    /**
     * @return array<string|object>
     */
    public function getRules(): array
    {
        return array_values($this->rules);
    }

    /**
     * @param array<string|object> $rules
     */
    public function setRules(array $rules): self
    {
        $this->rules = [];

        foreach ($rules as $rule) {
            $this->setRule($rule);
        }

        return $this;
    }

    /**
     * @param string|object $rule
     * @param null|mixed $value
     */
    public function setRule($rule, $value = null): self
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

        $this->rules[$rule] = $value;

        return $this;
    }

    /**
     * @param string|object $rule
     */
    public function removeRule($rule): self
    {
        unset($this->rules[$rule]);

        return $this;
    }
}

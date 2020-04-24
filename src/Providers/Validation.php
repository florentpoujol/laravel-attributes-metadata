<?php

namespace FlorentPoujol\LaravelModelMetadata\Providers;

use FlorentPoujol\LaravelModelMetadata\AttributeMetadata;

class Validation extends BaseProvider
{
    /** @var \FlorentPoujol\LaravelModelMetadata\AttributeMetadata */
    protected $attributeMetadata;

    /**
     * Keys are rule name, or Fqcn when they are objects.
     * Values are full rules (with "arguments" after the semicolon, if any) or instances.
     *
     * @var array<string, string|object>
     */
    protected $validationRules = [];

    protected function buildRulesFromMetadata()
    {
        $rules = [];
        $metas = $this->attributeMetadata->getMetas();

        foreach ($metas as $name => $value) {
            switch ($name) {
                case AttributeMetadata::UNSIGNED:
                    if ($value) {
                        $rules['numeric'] = null;
                        $rules['min'] = 0;
                    }
                    break;

                case AttributeMetadata::MIN_VALUE:
                    $rules['numeric'] = null;
                    $rules['min'] = $value;
                    break;
                case AttributeMetadata::MAX_VALUE:
                    $rules['numeric'] = null;
                    $rules['max'] = $value;
                    break;

                case AttributeMetadata::MIN_LENGTH:
                    $rules['string'] = null;
                    $rules['min'] = $value;
                    break;
                case AttributeMetadata::MAX_LENGTH:
                    $rules['string'] = null;
                    $rules['max'] = $value;
                    break;
            }
        }

        $this->validationRules = $rules;
    }

    /**
     * @return array<string|object>
     */
    public function getValidationRules(): array
    {
        return array_values($this->validationRules);
    }

    /**
     * @param array<string|object> $rules
     */
    public function setValidationRules(array $rules): self
    {
        $this->validationRules = [];

        foreach ($rules as $rule) {
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
            $rule = explode(':', $rule, 2)[0];
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
        unset($this->validationRules[$rule]);

        return $this;
    }

    protected $validationMessage = '';

    public function getValidationMessage(): string
    {
        return $this->validationMessage ?: '';
    }

    public function setValidationMessage(string $message): self
    {
        $this->validationMessage = $message;

        return $this;
    }
}

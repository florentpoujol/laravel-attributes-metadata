<?php

namespace FlorentPoujol\LaravelModelMetadata\Handlers\Validation;

use FlorentPoujol\LaravelModelMetadata\AttributeMetadata;
use FlorentPoujol\LaravelModelMetadata\Handlers\BaseHandler;

class Validation extends BaseHandler // aka AttributeMetadataCollectionHandler
{
    /**
     * Keys are rule name, or Fqcn when they are objects.
     * Values are full rules (with "arguments" after the semicolon, if any) or instances.
     *
     * @var array<string, string|object>
     */
    protected $validationRules = [];

    protected function buildRules(): void
    {
        $this->validationRules = [];

        $this->attributeMetadataCollection
            ->each(function (AttributeMetadata $attrMeta) {
                $rules = [];
                $metas = $attrMeta->all();
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

                $this->validationRules[$attrMeta->getName()] = $rules;
            });
    }

    /**
     * @return array<string|object>
     */
    public function getValidationRules(array $attributes = null): array
    {
        if (! empty($this->validationRules)) {
            $this->buildRules();
        }

        return $this->validationRules;
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
}

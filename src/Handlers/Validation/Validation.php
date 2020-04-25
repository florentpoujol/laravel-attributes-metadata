<?php

namespace FlorentPoujol\LaravelAttributeMetadata\Handlers\Validation;

use FlorentPoujol\LaravelAttributeMetadata\AttributeMetadata;
use FlorentPoujol\LaravelAttributeMetadata\Handlers\BaseHandler;

class Validation extends BaseHandler
{
    /**
     * Keys are rule name, or Fqcn when they are objects.
     * Values are full rules (with "arguments" after the semicolon, if any) or instances.
     *
     * @var array<string, array<string|object>>
     */
    protected $validationRules = [];

    /**
     * @param array<string> $attributes
     */
    protected function buildRules(array $attributes = []): void
    {
        $this->attributeMetadataCollection
            ->each(function (AttributeMetadata $attrMeta) use ($attributes) {
                $attrName = $attrMeta->getName();
                if (! empty($attributes) && ! in_array($attrName, $attributes)) {
                    return;
                }

                $metas = $attrMeta->all(); // all metadata of one attribute
                foreach ($metas as $name => $value) {
                    switch ($name) {
                        case AttributeMetadata::UNSIGNED:
                            if ($value) {
                                $this
                                    ->setRule($attrName, 'numeric')
                                    ->setRule($attrName, 'min', 0);
                            }
                            break;

                        case AttributeMetadata::NULLABLE:
                            $this->setRule($attrName, 'nullable');
                            break;
                        case AttributeMetadata::REQUIRED:
                            $this->setRule($attrName, 'required');
                            break;

                        case AttributeMetadata::MIN_VALUE:
                            $this
                                ->setRule($attrName, 'numeric')
                                ->setRule($attrName, 'min', $value);
                            break;
                        case AttributeMetadata::MAX_VALUE:
                            $this
                                ->setRule($attrName, 'numeric')
                                ->setRule($attrName, 'max', $value);
                            break;

                        case AttributeMetadata::MIN_LENGTH:
                            $this
                                ->setRule($attrName, 'string')
                                ->setRule($attrName, 'min', $value);
                            break;
                        case AttributeMetadata::MAX_LENGTH:
                            $this
                                ->setRule($attrName, 'string')
                                ->setRule($attrName, 'max', $value);
                            break;

                        case 'boolean':
                            $this->setRule($attrName, 'boolean');
                            break;
                    }
                }
            });
    }

    /**
     * @param string|object $rule
     * @param null|mixed $value
     */
    public function setRule(string $attributeName, $rule, $value = null): self
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

        $this->validationRules[$attributeName][$rule] = $value;

        return $this;
    }

    /**
     * @param string|object $rule
     */
    public function removeValidationRule(string $attributeName, $rule): self
    {
        unset($this->validationRules[$attributeName][$rule]);

        return $this;
    }

    /**
     * @return array<string, array<string|object>> Validation rules per attribute name
     */
    public function getValidationRules(array $attributes = []): array
    {
        if (empty($attributes)) {
            if (empty($this->validationRules)) {
                $this->buildRules();
            }

            return $this->validationRules;
        }

        $diff = array_diff($attributes, array_keys($this->validationRules));
        if (! empty($diff)) {
            $this->buildRules($diff);
        }

        return array_values(array_intersect_key($this->validationRules, $attributes));
    }
}

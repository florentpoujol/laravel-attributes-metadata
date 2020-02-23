<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

/**
 * To be added on form request classes.
 *
 * Expect two properties to be set on the class
 * - `metadataModelFqcn` with the FQCN of the validated model
 * - `validatedAttributes` (optional) with the list of attributes to validates
 *
 * @property string $metadataModelFqcn
 * @property string[] $validatedAttributes
 */
trait PopulatesFormRequestFromMetadata
{
    /**
     * @return array<string, string|array<string|object>>
     *
     * @throws \Exception When the 'metadataModelFqcn' isn't found on the form request instance
     */
    public function rules(): array
    {
        if (! property_exists($this, 'metadataModelFqcn')) {
            $fqcn = static::class;
            throw new \Exception("Missing property 'metadataModelFqcn' on form request '$fqcn'.");
        }

        /** @var \FlorentPoujol\LaravelModelMetadata\ModelMetadata $modelMeta */
        $modelMeta = $this->metadataModelFqcn::getMetadata();

        $validatedAttrs = [];
        if (property_exists($this, 'validatedAttributes')) {
            $validatedAttrs = $this->validatedAttributes;
        }

        // TODO only return the creation rules when the http method is POST
        return $modelMeta->getCreateValidationRules($validatedAttrs);
    }

    /**
     * @return array<string, array<string|object>>
     *
     * @throws \Exception When the 'metadataModelFqcn' isn't found on the form request instance
     */
    public function messages(): array
    {
        if (! property_exists($this, 'metadataModelFqcn')) {
            $fqcn = static::class;
            throw new \Exception("Missing property 'metadataModelFqcn' on form request '$fqcn'.");
        }

        /** @var \FlorentPoujol\LaravelModelMetadata\ModelMetadata $modelMeta */
        $modelMeta = $this->metadataModelFqcn::getMetadata();

        $validatedAttrs = [];
        if (property_exists($this, 'validatedAttributes')) {
            $validatedAttrs = $this->validatedAttributes;
        }

        return $modelMeta->getCreateValidationMessages($validatedAttrs);
    }
}


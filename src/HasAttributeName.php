<?php

namespace FlorentPoujol\LaravelAttributePresets;

trait HasAttributeName
{
    /** @var null|string The name of the attribute. */
    protected $attributeName;

    public function setAttributeName(string $attributeName): self
    {
        $this->attributeName = $attributeName;

        return $this;
    }

    public function getAttributeName(): ?string
    {
        return $this->attributeName;
    }


}

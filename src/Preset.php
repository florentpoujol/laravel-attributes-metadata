<?php

namespace FlorentPoujol\LaravelAttributePresets;

use Illuminate\Database\Schema\Blueprint;
use Laravel\Nova\Fields\Field;

interface Preset
{
    /**
     * @return static
     */
    public function setName(string $attributeName);
    public function getName(): ?string;

    //--
    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     *
     * @return static
     */
    public function addToTable(Blueprint $table);
    public function hasDbColumn(): bool;

    /**
     * @return array<string|\Illuminate\Validation\Rule>
     */
    public function getValidationRules(): array;
    public function getValidationMessage(): ?string;

    /**
     * @return null|\Laravel\Nova\Fields\Field
     */
    public function getNovaField(): ?Field;
}

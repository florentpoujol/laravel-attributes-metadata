<?php

namespace FlorentPoujol\LaravelAttributePresets;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Schema\Blueprint;
use Laravel\Nova\Fields\Field;

interface Preset
{
    /**
     * @return static
     */
    public function setName(string $attributeName);
    public function getName(): ?string;

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
    public function hasNovaField(): bool;

    public function isRelation(): bool;
    public function getRelationMethod(): ?string;
    public function getRelationInstance(): Relation;

    public function getCast(): ?string;
    public function hasCast(): bool;

    public function isFillable(): bool;
    public function isGuarded(): bool;
    public function isHidden(): bool;
    public function isDate(): bool;

    /**
     * @return null|mixed
     */
    public function getDefaultValue();
    public function hasDefaultValue(): bool;

    public function isPrimaryKey(): bool;
    public function getPrimaryKeyType(): ?string;
    public function isIncrementingPrimaryKey(): bool;
}

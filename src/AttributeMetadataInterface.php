<?php

namespace FlorentPoujol\LaravelModelMetadata;

interface AttributeMetadataInterface
{
    // --------------------------------------------------
    // validation

    protected $columnDefinitions = [];
    public function addColumnToTable(Blueprint $table): array;
    public function updateColumnFromTable(Blueprint $table): array;
    public function dropColumnIfExists(): array;
    

    // --------------------------------------------------
    // validation

    protected $validationRules = [];
    public function getValidationRules(): array;

    public function setValidationRules(array $rules): self;
    public function addValidationRule(string|object $rule): self;
    public function removeValidationRule(string|object $rule): self;

    protected $validationMessage = '';
    public function getValidationMessage(): array;
    public function setValidationMessage(array $messages): self;


    // --------------------------------------------------
    // Nova

    protected $novaFields = [];    
    public function getNovaFields(): array;
    public function getNovaIndexField(): Field;
    public function getNovaDetailsField(): Field;
    public function getNovaCreateField(): Field;
    public function getNovaUpdateField(): Field;

    public function setNovaFields(array $fields): self;


    // --------------------------------------------------
    // cast and mutators

    public function setCast(string|object $cast = null): bool;
    public function hasCast(): bool;
    public function getCast(): ?(string|object);

    public function setCastTarget(string $castTarget = null): bool;
    public function hasCastTarget(): bool;
    public function getCastTarget(): ?string;

    public function markHasSetter(bool $hasSetter = true): self;
    public function hasSetter(): bool;

    public function markHasGetter(bool $hasGetter = true): self;
    public function hasGetter(): bool;


    // --------------------------------------------------
    // relations

    public function setRelation(string $related = null, array $constructorArgs = [])
    public function getRelation(): ?Relation;

    public function isRelation(): bool;

    // --------------------------------------------------
    // API to resolve model metadatas

    public function markHidden(bool $isHidden = true): self;
    public function isHidden(): bool;

    public function markGuarded(bool $isGuarded = true): self;
    public function isGuarded(): bool;

    public function markFillable(bool $isFillable = true): self;
    public function isFillable(): bool;

    public function markPrimaryKey(bool $isPrimaryKey = true, bool $isInt = true, bool $isIncrementing = true): self;
    public function markPrimaryKey(array $primaryKeyParams = ['int', 'incrementing']): self;
    public function isPrimaryKey(): bool;
    public function isIncrementingPrimaryKey(): bool;
    public function isIntPrimaryKey(): bool;

    public function markDate(bool $isDate = true): self;
    public function isDate(): bool;

    public function markNullable(bool $isNullable = true): self;
    public function isNullable(): bool;

    public function markRequired(bool $isRequired = true): self;
    public function isRequired(): bool;

    public function setDefaultValue($value): self;
    public function getDefaultValue();
    public function hasDefaultValue(): bool;

    // on ints
    public function markUnsigned(bool $isUnsigned = true): self;
    public function isUnsigned(): bool;
}

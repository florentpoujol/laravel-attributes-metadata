<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets;

use FlorentPoujol\LaravelAttributePresets\PresetTraits\ProvidesColumnDefinitions;
use FlorentPoujol\LaravelAttributePresets\PresetTraits\ProvidesNovaFields;
use FlorentPoujol\LaravelAttributePresets\PresetTraits\ProvidesValidation;

class BasePreset
{
    /** @var null|string The name of the attribute. Usually set from ModelMetadata->getAttributeMetadata()`. */
    protected $name;

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    use ProvidesValidation;
    use ProvidesColumnDefinitions;
    use ProvidesNovaFields;

    // --------------------------------------------------
    // cast and mutators

    /** @var null|string|object */
    protected $cast;

    /**
     * @param null|string|object $cast
     * @param string $value For casts that have values, like decimal or datetime
     */
    public function setCast($cast, string $value = null): self
    {
        if ($value !== null) {
            $cast .= ":$value";
        }

        $this->cast = $cast;

        return $this;
    }

    public function hasCast(): bool
    {
        return $this->cast !== null;
    }

    /**
     * @return null|object|string
     */
    public function getCast()
    {
        return $this->cast;
    }

    /** @var null|string */
    protected $castTarget;

    public function setCastTarget(string $castTarget)
    {
        $this->castTarget = $castTarget;

        return $this;
    }

    public function hasCastTarget(): bool
    {
        return $this->castTarget !== null;
    }

    public function getCastTarget(): ?string
    {
        return $this->castTarget;
    }

    protected $hasSetter = false;

    public function markHasSetter(bool $hasSetter = true): self
    {
        $this->hasSetter = $hasSetter;

        return $this;
    }

    public function hasSetter(): bool
    {
        return $this->hasSetter;
    }

    protected $hasGetter = false;

    public function markHasGetter(bool $hasGetter = true): self
    {
        $this->hasGetter = $hasGetter;

        return $this;
    }

    public function hasGetter(): bool
    {
        return $this->hasGetter;
    }

    // --------------------------------------------------
    // Relation

    /** @var null|string The name of the relation, on the base Eloquent model that return the relation instance */
    protected $relationMethod;

    /** @var array<string> The arguments for the relation method factory */
    protected $relationParams = [];

    /**
     * @param null|string $method
     * @param array<string> $params
     */
    public function setRelation(string $method = null, array $params = []): self
    {
        $this->relationMethod = $method;
        $this->relationParams = $params;

        $this
            ->setNovaFieldType($method)
            ->setNovaFieldDefinition('searchable')
            ->removeNovaFieldDefinition('sortable');

        return $this;
    }

    /**
     * @return array<string>
     */
    public function getRelation(): array
    {
        return ['method' => $this->relationMethod, 'parameters' => $this->relationParams];
    }

    public function isRelation(): bool
    {
        return $this->relationMethod !== null;
    }

    // --------------------------------------------------
    // Hidden

    protected $isHidden = false;

    public function markHidden(bool $isHidden = true): self
    {
        $this->isHidden = $isHidden;

        if ($isHidden) {
            $this
                ->setNovaFieldDefinition('hideFromIndex')
                ->setNovaFieldDefinition('hideFromDetails');
        }

        return $this;
    }

    public function isHidden(): bool
    {
        return $this->isHidden;
    }

    // --------------------------------------------------
    // Guarded

    protected $isGuarded = false;

    public function markGuarded(bool $isGuarded = true): self
    {
        $this->isGuarded = $isGuarded;

        return $this;
    }

    public function isGuarded(): bool
    {
        return $this->isGuarded;
    }

    // --------------------------------------------------
    // Fillable

    protected $isFillable = true;

    public function markFillable(bool $isFillable = true): self
    {
        $this->isFillable = $isFillable;

        return $this;
    }

    public function isFillable(): bool
    {
        return $this->isFillable;
    }

    // --------------------------------------------------
    // Date

    protected $isDate = false;

    public function markDate(bool $isDate = true): self
    {
        $this->isDate = $isDate;

        return $this;
    }

    public function isDate(): bool
    {
        return $this->isDate;
    }

    // --------------------------------------------------
    // Nullable

    protected $isNullable = false;

    public function markNullable(bool $isNullable = true, bool $affectDbColumn = false): self
    {
        $this->isNullable = $isNullable;

        if ($isNullable) {
            $this
                ->setValidationRule('nullable')
                ->setNovaFieldDefinition('nullable');

            if ($affectDbColumn) {
                $this->addColumnDefinition('nullable');
            }
        } else {
            $this
                ->removeValidationRule('nullable')
                ->removeNovaFieldDefinition('nullable');

            if ($affectDbColumn) {
                $this->removeColumnDefinition('nullable');
            }
        }

        return $this;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    // --------------------------------------------------
    // Required

    protected $isRequired = false;

    /**
     * Mark/unmark the validation rule and the Nova field as being 'required'
     */
    public function markRequired(bool $isRequired = true): self
    {
        $this->isRequired = $isRequired;

        if ($isRequired) {
            $this
                ->setValidationRule('required')
                ->setNovaFieldDefinition('required');
        } else {
            $this
                ->removeValidationRule('required')
                ->removeNovaFieldDefinition('required');
        }

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->isRequired;
    }

    // --------------------------------------------------
    // Unsigned

    protected $isUnsigned = false;

    /**
     * Mark/unmark
     * - the field definition as being unsigned (only positive)
     * - the validation rule as being 'numeric' and 'min:0'
     */
    public function markUnsigned(bool $isUnsigned = true): self
    {
        $this->isUnsigned = $isUnsigned;

        if ($isUnsigned) {
            $this->getColumnDefinitions()->unsigned();
            $this
                ->setValidationRule('numeric', 0)
                ->setMinValue(0);
        } else {
            $this->setMinValue(null);

            $this->getColumnDefinitions()->removeDefinition('unsigned');
        }

        return $this;
    }

    public function isUnsigned(): bool
    {
        return $this->isUnsigned;
    }

    // --------------------------------------------------
    // Default value

    /** @var null|mixed */
    protected $defaultValue;

    /**
     * @param null|mixed $value
     */
    public function setDefaultValue($value, bool $affectDbColumn = false): self
    {
        $this->defaultValue = $value;

        if ($affectDbColumn) {
            $this->getColumnDefinitions()->default($value);
        }

        return $this;
    }

    /**
     * @return null|mixed
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function hasDefaultValue(): bool
    {
        return $this->defaultValue !== null;
    }

    // --------------------------------------------------
    // Primary key

    protected $isPrimaryKey = false;
    protected $primaryKeyType = 'int';
    protected $isIncrementingPrimaryKey = true;

    public function markPrimaryKey(bool $isPrimaryKey = true, string $keyType = 'int', bool $isIncrementing = true): self
    {
        $this->isPrimaryKey = $isPrimaryKey;
        $this->primaryKeyType = $keyType;
        $this->isIncrementingPrimaryKey = $this->primaryKeyType === 'int' ? $isIncrementing : false;

        if ($this->isPrimaryKey) {
            $this->getColumnDefinitions()->primary();
            $this->setNovaFieldType('id');
        } else {
            $this->getColumnDefinitions()->removeDefinition('primary');
            $this->getColumnDefinitions()->removeDefinition('autoIncrement');
        }

        if ($this->isIncrementingPrimaryKey) {
            $this->getColumnDefinitions()->autoIncrement();
        }

        return $this;
    }

    public function isPrimaryKey(): bool
    {
        return $this->isPrimaryKey;
    }

    public function getPrimaryKeyType(): string
    {
        return $this->primaryKeyType;
    }

    public function isIncrementingPrimaryKey(): bool
    {
        return $this->isIncrementingPrimaryKey;
    }

    // --------------------------------------------------
    // Min / Max / Step

    /** @var int|float */
    protected $minValue;

    /**
     * @param null|int|float $value
     */
    public function setMinValue($value): self
    {
        $this->minValue = $value;

        if ($value !== null) {
            if ($value < 0 && $this->isUnsigned()) {
                throw new \LogicException("Provided minimum value is '$value' but attribute is marked as unsigned.");
            }

            $this
                ->setValidationRule('min', $value)
                ->setNovaFieldDefinition('min', $value);
        } else {
            $this
                ->removeValidationRule('min')
                ->removeNovaFieldDefinition('min');
        }

        return $this;
    }

    /**
     * @return null|int|float
     */
    public function getMinValue()
    {
        return $this->minValue;
    }

    /** @var int|float */
    protected $maxValue;

    /**
     * @param null|int|float $value
     */
    public function setMaxValue($value): self
    {
        $this->maxValue = $value;

        if ($value !== null) {
            $this
                ->setValidationRule('max', $value)
                ->setNovaFieldDefinition('max', $value);
        } else {
            $this
                ->removeValidationRule('max')
                ->removeNovaFieldDefinition('max');
        }

        return $this;
    }

    /**
     * @param null|int
     */
    public function setMaxLength($value): self
    {
        $this->maxValue = $value;

        if ($value !== null) {
            $this->setValidationRule('max', $value);
        } else {
            $this->removeValidationRule('max');
        }

        $columnType = $this->getColumnDefinitions()->getType();
        if ($columnType === 'string' || $columnType === 'char') {
            $this->getColumnDefinitions()->setType($columnType, $value);
        }

        return $this;
    }

    /**
     * @return null|int|float
     */
    public function getMaxValue()
    {
        return $this->maxValue;
    }

    /** @var null|int|float */
    protected $step;

    /**
     * @param null|int|float $value
     */
    public function setStep($value): self
    {
        $this->step = $value;

        if ($value !== null) {
            $this->setNovaFieldDefinition('step', $value);
        } else {
            $this->removeNovaFieldDefinition('step');
        }

        return $this;
    }

    /**
     * @return null|int|float
     */
    public function getStep()
    {
        return $this->step;
    }
}

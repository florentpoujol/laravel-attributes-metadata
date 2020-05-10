<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\PresetTraits;

/**
 * @method $this cast(string $cast, $value = null)
 * @method ?string getCast()
 * @method bool hasCast()
 *
 * @method $this default(mixed $value)
 * @method $this getDefault(mixed $value)
 * @method $this hasDefault(mixed $value)
 */
trait ProvidesModelMetadata
{
    // --------------------------------------------------

    protected $isHidden = false;

    public function markHidden(bool $isHidden = true): self
    {
        $this->isHidden = $isHidden;

        if ($isHidden) {
            $this
                ->getNovaField()
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
}

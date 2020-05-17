<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\PresetTraits;

/**
 * @method $this cast(string $cast, $value = null)
 * @method null|string getCast()
 * @method bool hasCast()
 *
 * @method $this fillable(bool $fillable = true)
 * @method bool isFillable()
 *
 * @method $this guarded(bool $guarded = true)
 * @method bool isGuarded()
 *
 * @method $this hidden(bool $hidden = true)
 * @method bool isHidden()
 *
 * @method $this date(bool $date = true)
 * @method bool isDate()
 *
 * @method $this default(mixed $value)
 * @method $this getDefaultValue(mixed $value)
 * @method $this hasDefaultValue(mixed $value)
 */
trait ProvidesModelMetadata
{
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

    // --------------------------------------------------

    public function primaryKey(bool $isPrimaryKey = true, string $keyType = 'int', bool $isIncrementing = true): self
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

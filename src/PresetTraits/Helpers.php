<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\PresetTraits;

trait Helpers
{
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

    // --------------------------------------------------

    public function markGuarded(bool $isGuarded = true): self
    {
        $this->isGuarded = $isGuarded;

        return $this;
    }
    // --------------------------------------------------

    public function markFillable(bool $isFillable = true): self
    {
        $this->isFillable = $isFillable;

        return $this;
    }


    // --------------------------------------------------

    public function markDate(bool $isDate = true): self
    {
        $this->isDate = $isDate;

        return $this;
    }


    protected $isNullable = false;

    public function markNullable(bool $isNullable = true, bool $affectDbColumn = false): self
    {
        $this->isNullable = $isNullable;

        if ($isNullable) {
            $this
                ->setValidationRule('nullable')
                ->setNovaFieldDefinition('nullable');

            if ($affectDbColumn) {
                $this->getColumnDefinitions()->nullable();
            }
        } else {
            $this
                ->removeValidationRule('nullable')
                ->removeNovaFieldDefinition('nullable');

            if ($affectDbColumn) {
                $this->getColumnDefinitions()->removeDefinition('nullable');
            }
        }

        return $this;
    }

    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    // --------------------------------------------------

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

<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributeMetadata;

use Illuminate\Support\Collection;

class AttributeMetadata extends Collection
{
    /** @var array<string, null|mixed> */
    protected $items = [];

    public function add($item): self
    {
        if (! $this->has($item)) {
            $this->items[$item] = null;
        }

        return $this;
    }

    /**
     * @param null|mixed $value
     */
    protected function unsetIfEmpty(string $name, $value): void
    {
        if (empty($value)) {
            unset($this->items[$name]);

            return;
        }

        $this->put($name, $value);
    }

    // --------------------------------------------------

   /** @var null|string The name of the attribute. Usually set when from AttributeMetadataCollection->get()`. */
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

    // --------------------------------------------------
    // cast and mutators

    public const CAST = 'cast';

    /**
     * @param null|string|\Illuminate\Contracts\Database\Eloquent\CastsAttributes $cast
     * @param string|array $value For casts that have values, like decimal or datetime
     */
    public function setCast($cast, $value = null): self
    {
        if ($cast === null) {
            $this->unsetIfEmpty(self::CAST, null);

            return $this;
        }

        if ($value !== null) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }

            $cast .= ":$value";
        }

        $this->put(self::CAST, $cast);

        return $this;
    }

    public function hasCast(): bool
    {
        return $this->has(self::CAST);
    }

    /**
     * @return null|object|string
     */
    public function getCast()
    {
        return $this->get(self::CAST);
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

    public const RELATION = 'relation';

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
        if ($method === null) {
            $this->unsetIfEmpty(self::RELATION, null);

            return $this;
        }

        $this->put(self::RELATION, [
            'method' => $this->relationMethod,
            'parameters' => $this->relationParams
        ]);

        // $this
        //     ->setNovaFieldType($method)
        //     ->setNovaFieldDefinition('searchable')
        //     ->removeNovaFieldDefinition('sortable');

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

    public const GUARDED = 'guarded';

    public function markGuarded(bool $isGuarded = true): self
    {
        $this->unsetIfEmpty(self::GUARDED, $isGuarded);

        return $this;
    }

    public function isGuarded(): bool
    {
        return $this->get(self::GUARDED, false);
    }

    // --------------------------------------------------

    public const FILLABLE = 'FILLABLE';

    public function markFillable(bool $isFillable = true): self
    {
        $this->unsetIfEmpty(self::FILLABLE, $isFillable);

        return $this;
    }

    public function isFillable(): bool
    {
        return $this->get(self::FILLABLE, false);
    }

    // --------------------------------------------------

    public const DATE = 'date';

    public function markDate(bool $isDate = true): self
    {
        $this->unsetIfEmpty(self::DATE, $isDate);

        return $this;
    }

    public function isDate(): bool
    {
        return $this->get(self::DATE, false);
    }

    // --------------------------------------------------
    // Nullable

    public const NULLABLE = 'nullable';

    public function markNullable(bool $isNullable = true, bool $affectDbColumn = false): self
    {
        $this->unsetIfEmpty(self::NULLABLE, $isNullable);

        return $this;
    }

    public function isNullable(): bool
    {
        return $this->get(self::NULLABLE, false);
    }

    // --------------------------------------------------
    // Required

    public const REQUIRED = 'required';

    public function markRequired(bool $isRequired = true): self
    {
        $this->unsetIfEmpty(self::REQUIRED, $isRequired);

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->get(self::REQUIRED, false);
    }

    // --------------------------------------------------
    // Unsigned

    public const UNSIGNED = 'unsigned';

    public function markUnsigned(bool $isUnsigned = true): self
    {
        $this->unsetIfEmpty(self::UNSIGNED, $isUnsigned);

        return $this;
    }

    public function isUnsigned(): bool
    {
        return $this->get(self::UNSIGNED, false);
    }

    // --------------------------------------------------

    public const DEFAULT_VALUE = 'default_value';

    /**
     * @param null|mixed $value
     */
    public function setDefaultValue($value, bool $affectDbColumn = false): self
    {
        $this->put(self::DEFAULT_VALUE, $value);

        // if ($affectDbColumn) {
        //     $this->addColumnDefinition('default', $value);
        // }
        //
        // $this->__call('setDefaultValue', $value);

        return $this;
    }

    /**
     * @return null|mixed
     */
    public function getDefaultValue()
    {
        return $this->get(self::DEFAULT_VALUE);
    }

    public function hasDefaultValue(): bool
    {
        return $this->has(self::DEFAULT_VALUE);
    }

    // --------------------------------------------------

    public const MIN_VALUE = 'min_value';

    /**
     * @param null|int|float $value
     */
    public function setMinValue($value): self
    {
        $this->unsetIfEmpty(self::MIN_VALUE, $value);

        return $this;
    }

    /**
     * @return null|int|float
     */
    public function getMinValue()
    {
        return $this->get(self::MIN_VALUE);
    }

    public const MAX_VALUE = 'max_value';

    /**
     * @param null|int|float $value
     */
    public function setMaxValue($value): self
    {
        $this->unsetIfEmpty(self::MAX_VALUE, $value);

        return $this;
    }

    /**
     * @return null|int|float
     */
    public function getMaxValue()
    {
        return $this->get(self::MAX_VALUE);
    }

    public const STEP = 'step';

    /**
     * @param null|int|float $value
     */
    public function setStep($value): self
    {
        $this->unsetIfEmpty(self::STEP, $value);

        // if ($value !== null) {
        //     $this->setNovaFieldDefinition('step', $value);
        // } else {
        //     $this->removeNovaFieldDefinition('step');
        // }

        return $this;
    }

    /**
     * @return null|int|float
     */
    public function getStep()
    {
        return $this->metas[self::STEP] ?? null;
    }

    public const MIN_LENGTH = 'min_length';
    /**
     * @param null|int
     */
    public function setMinLength($value): self
    {
        $this->unsetIfEmpty(self::MAX_LENGTH, $value);

        return $this;
    }

    /**
     * @return null|int|float
     */
    public function getMinLength()
    {
        return $this->metas[self::MAX_LENGTH] ?? null;
    }

    public const MAX_LENGTH = 'max_length';
    /**
     * @param null|int
     */
    public function setMaxLength($value): self
    {
        $this->unsetIfEmpty(self::MAX_LENGTH, $value);

        return $this;
    }

    /**
     * @return null|int|float
     */
    public function getMaxLength()
    {
        return $this->get(self::MAX_LENGTH);
    }

    // --------------------------------------------------

    public const BOOLEAN = 'boolean';

    public function markBoolean(bool $isBoolean = true): self
    {
        $this->unsetIfEmpty(self::BOOLEAN, $isBoolean);

        return $this;
    }

    public function isBoolean(): bool
    {
        return $this->get(self::BOOLEAN, false);
    }
}

<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

use FlorentPoujol\LaravelModelMetadata\Providers\BaseProvider;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Laravel\Nova\Fields\Field;

class AttributeMetadata
{
    /** @var array<string, mixed> */
    protected static $makeDefinitions = [];

    public static function make(array $definitions): string
    {
        static::$makeDefinitions = $definitions;

        return static::class;
    }

    public function setupFromMakeDefinitions(): void
    {
        foreach (static::$makeDefinitions as $methodName => $arguments) {
            if (is_int($methodName)) {
                $methodName = $arguments;
                $arguments = [];
            }

            if ($arguments === null) {
                $arguments = [];
            } elseif (! is_array($arguments)) {
                $arguments = [$arguments];
            }

            $this->$methodName(...$arguments);
        }
    }

    public function __construct()
    {
        $this->setupFromMakeDefinitions();
    }

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

    // --------------------------------------------------
    // Providers

    /** @var array<string, \FlorentPoujol\LaravelModelMetadata\Providers\BaseProvider>  */
    protected $providers = [];

    /**
     * @param \FlorentPoujol\LaravelModelMetadata\Providers\BaseProvider $provider
     */
    public function addProvider(BaseProvider $provider)
    {
        $this->providers[get_class($provider)] = $provider;
    }

    public function getProvider(string $providerFqcn): ?BaseProvider
    {
        return $this->providers[$providerFqcn] ?? null;
    }

    /**
     * @return array<string, \FlorentPoujol\LaravelModelMetadata\Providers\BaseProvider>
     */
    public function getProviders(): array
    {
        return $this->providers;
    }

    /**
     * @param mixed ...$args
     *
     * @return mixed
     */
    public function __call(string $method, ...$args)
    {
        $isGetter = strpos($method, 'get') === 0;

        foreach ($this->getProviders() as $provider) {
            if (method_exists($provider, $method)) {
                $returnValue = $provider->$method(...$args);

                if ($isGetter) {
                    return $returnValue;
                }
            }
        }

        return null;
    }

    // --------------------------------------------------
    // Raw

    /**
     * @var array<string, mixed> Raw list of metadatas ans an associative array
     */
    protected $raw = [];

    public function hasMeta(string $name): bool
    {
        return array_key_exists($name, $this->raw);
    }

    /**
     * @return null|mixed $value
     */
    public function setMeta(string $name, $value)
    {
        return $this->raw[$name] = $value;
    }

    /**
     * @return null|mixed
     */
    public function getMeta(string $name, $default = null)
    {
        return $this->raw[$name] ?? $default;
    }

    public function getMetas(): array
    {
        return $this->raw;
    }

    protected function unsetIfEmpty(string $name, $value): void
    {
        if (empty($value)) {
            unset($this->raw[$name]);

            return;
        }

        $this->raw[$name] = $value;
    }

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

        $this->setMeta(self::RELATION, [
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

    public const FILLABLE = 'FILLABLE';

    public function setFillable(bool $isFillable = true): self
    {
        $this->unsetIfEmpty(self::FILLABLE, $isFillable);

        return $this;
    }

    public function isFillable(): bool
    {
        return $this->hasMeta(self::FILLABLE);
    }

    // --------------------------------------------------
    // Date

    public const DATE = 'date';

    public function setDate(bool $isDate = true): self
    {
        $this->unsetIfEmpty(self::DATE, $isDate);

        return $this;
    }

    public function isDate(): bool
    {
        return $this->getMeta(self::DATE, false);
    }

    // --------------------------------------------------
    // Nullable

    public const NULLABLE = 'nullable';

    public function setNullable(bool $isNullable = true, bool $affectDbColumn = false): self
    {
        $this->isNullable = $isNullable;

        // if ($isNullable) {
        //     $this
        //         ->setValidationRule('nullable')
        //         ->setNovaFieldDefinition('nullable');
        //
        //     if ($affectDbColumn) {
        //         $this->addColumnDefinition('nullable');
        //     }
        // } else {
        //     $this
        //         ->removeValidationRule('nullable')
        //         ->removeNovaFieldDefinition('nullable');
        //
        //     if ($affectDbColumn) {
        //         $this->removeColumnDefinition('nullable');
        //     }
        // }

        return $this;
    }

    public function isNullable(): bool
    {
        return $this->raw[self::NULLABLE] ?? false;
    }

    // --------------------------------------------------
    // Required

    public const REQUIRED = 'required';

    /**
     * Mark/unmark the validation rule and the Nova field as being 'required'
     */
    public function setRequired(bool $isRequired = true): self
    {
        $this->raw[self::REQUIRED] = $isRequired;

        // if ($isRequired) {
        //     $this
        //         ->setValidationRule('required')
        //         ->setNovaFieldDefinition('required');
        // } else {
        //     $this
        //         ->removeValidationRule('required')
        //         ->removeNovaFieldDefinition('required');
        // }

        return $this;
    }

    public function isRequired(): bool
    {
        return $this->raw[self::REQUIRED] ?? false;
    }

    // --------------------------------------------------
    // Unsigned

    public const UNSIGNED = 'unsigned';

    /**
     * Mark/unmark
     * - the field definition as being unsigned (only positive)
     * - the validation rule as being 'numeric' and 'min:0'
     */
    public function setUnsigned(bool $isUnsigned = true): self
    {
        $this->unsetIfEmpty(self::UNSIGNED, $isUnsigned);

        // if ($isUnsigned) {
        //     $this
        //         ->addColumnDefinition('unsigned')
        //         ->setValidationRule('numeric', 0)
        //         ->setMinValue(0);
        // } else {
        //     $this
        //         ->removeColumnDefinition('unsigned')
        //         ->setMinValue(null);
        // }

        return $this;
    }

    public function isUnsigned(): bool
    {
        return $this->getMeta(self::UNSIGNED, false);
    }

    // --------------------------------------------------
    // Default value

    public const DEFAULT_VALUE = 'default_value';

    /**
     * @param null|mixed $value
     */
    public function setDefaultValue($value, bool $affectDbColumn = false): self
    {
        $this->unsetIfEmpty(self::DEFAULT_VALUE, $value);

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
        return $this->getMeta(self::DEFAULT_VALUE);
    }

    public function hasDefaultValue(): bool
    {
        return $this->hasMeta(self::DEFAULT_VALUE);
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
            $this
                ->addColumnDefinition('primary')
                ->setNovaFieldType('id');
        } else {
            $this
                ->removeColumnDefinition('primary')
                ->removeColumnDefinition('autoIncrement');
        }

        if ($this->isIncrementingPrimaryKey) {
            $this->addColumnDefinition('autoIncrement');
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
        return $this->raw[self::MAX_VALUE] ?? null;
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
        return $this->raw[self::MAX_VALUE] ?? null;
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
        return $this->raw[self::STEP] ?? null;
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
        return $this->raw[self::MAX_LENGTH] ?? null;
    }

    public const MAX_LENGTH = 'max_length';
    /**
     * @param null|int
     */
    public function setMaxLength($value): self
    {
        $this->unsetIfEmpty(self::MAX_LENGTH, $value);

        // if ($value !== null) {
        //     $this->setValidationRule('max', $value);
        // } else {
        //     $this->removeValidationRule('max');
        // }
        //
        // $columnType = $this->getColumnType();
        // if ($columnType === 'string' || $columnType === 'char') {
        //     $this->setColumnType($columnType, $value);
        // }

        return $this;
    }

    /**
     * @return null|int|float
     */
    public function getMaxLength()
    {
        return $this->raw[self::MAX_LENGTH] ?? null;
    }
}

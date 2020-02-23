<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelModelMetadata;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\ColumnDefinition;
use Illuminate\Support\Str;

class AttributeMetadata
{
    public function __construct(array $metadata)
    {
        $this->metadata = $metadata;
    }

    protected $metadata;

    protected $metadataNormalized = false;

    protected function normalizeMetadata(): void
    {
        if ($this->metadataNormalized) {
            return;
        }

        // take the metadata an turn the numerically indexed values into string key = null value
        $copy = $this->metadata;
        foreach ($copy as $key => $value) {
            if (is_string($key)) {
                continue;
            }

            $this->metadata[$value] = null;
            unset($this->metadata[$key]);
        }

        $this->metadataNormalized = true;
    }

    public function resolve(): void
    {
        $this->resolveColumnDefinition();
        $this->resolveValidationRules();
        // ...
    }

    // --------------------------------------------------
    // Attribute name

    /** @var null|string The name of the attribute */
    protected $name;

    protected function resolveName(): void
    {
        $parts = explode('\\', static::class);

        $camelName = str_replace('Metadata', '', end($parts));

        $this->name = Str::snake($camelName); // CamelCase to snake_case
    }

    public function getName(): string
    {
        if ($this->name === null) {
            $this->resolveName();
        }

        return $this->name;
    }

    // --------------------------------------------------
    // SQL table column definitions

    /** @var null|array<string, null|mixed> */
    protected $columnDefinitionsArray;

    /** @var null|ColumnDefinition */
    protected $columnDefinition;

    /** @var null|array<string, array<string, null|mixed>> */
    protected static $availableMethods;

    protected $existsInDb = false;

    protected function resolveAvailableColumnDefinitionMethods(): void
    {
        if (static::$availableMethods !== null) {
            return;
        }

        // resolve all public methods of the Blueprint and ColumnDefinition class
        $reflClass = new \ReflectionClass(Blueprint::class);
        $reflMethods = $reflClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        /** @var \ReflectionMethod $reflMethod */
        foreach ($reflMethods as $reflMethod) {
            static::$availableMethods['table'][] = $reflMethod->getName();
        }

        // now get the one from the ColumnDefinition PHPDoc
        $reflClass = new \ReflectionClass(ColumnDefinition::class);
        $commentBlock = $reflClass->getDocComment();
        if ($commentBlock === false) {
            return;
        }

        $pattern = '/method ColumnDefinition ([a-zA-Z0-9]+)\(/';
        $matches = [];
        preg_match_all($pattern, $commentBlock, $matches, PREG_PATTERN_ORDER);

        $methods = $matches[1] ?? [];
        // remove the methods that already exists in the table array (unique for instance)
        $methods = array_diff($methods, static::$availableMethods['table']);

        foreach ($methods as $method) {
            static::$availableMethods['column'][] = $method;
        }
    }

    protected function resolveColumnDefinition(): void
    {
        if (
            $this->metadata[0] === '_dynamic' ||
            $this->columnDefinitionsArray !== null
        ) {
            return;
        }

        if (static::$availableMethods === null) {
            $this->resolveAvailableColumnDefinitionMethods();
        }

        $this->columnDefinitionsArray['table'] = array_intersect_key(
            $this->metadata,
            static::$availableMethods['table']
        );

        if (
            isset($this->columnDefinitionsArray['table']['unique']) &&
            $this->columnDefinitionsArray['table']['unique'] !== null
        ) {
            // 'unique' is also a validation rules, but the rule always has a value
            unset($this->columnDefinitionsArray['table']['unique']);
        }

        $this->columnDefinitionsArray['column'] = array_intersect_key(
            $this->metadata,
            static::$availableMethods['column']
        );

        if (
            isset($this->columnDefinitionsArray['column']['after']) &&
            strtotime($this->columnDefinitionsArray['column']['after']) === false
        ) {
            // 'after' is also a validation rule, and both can have an argument/value
            // except that the validation rule always as a value parsable by strtotime()
            unset($this->columnDefinitionsArray['column']['after']);
        }

        // detect when a field should be marked as json
        $keys = array_keys($this->metadata);
        $casts = ['object' => null, 'array' => null, 'collection' => null];
        $fieldType = ['json' => null, 'text' => null, 'mediumText' => null, 'longText' => null];

        if (
            !empty(array_intersect_key($casts, $keys)) && // if the field is marked as object, array or collection
            empty(array_intersect_key($fieldType, $keys)) // but isn't marked as a text-type field
        ) {
            // then mark it as JSON
            $this->columnDefinitionsArray['table'] = ['json' => null] + $this->columnDefinitionsArray['table'];
        }

        $this->existsInDb = true;
    }

    /**
     * @param \Illuminate\Database\Schema\Blueprint $table
     *
     * @return null|ColumnDefinition
     */
    public function addColumnDefinition(Blueprint $table): ?ColumnDefinition
    {
        if ($this->columnDefinitionsArray === null) {
            $this->resolveColumnDefinition();
        }

        if (! $this->existsInDb) {
            return null;
        }

        // first extract a method that can be called on the Blueprint object
        $tableMethods = $this->columnDefinitionsArray['table'];
        $first = array_keys($tableMethods)[0];

        if ($tableMethods[$first] !== null) {
            $this->columnDefinition = $table->$first($tableMethods[$first]);
        } else {
            $this->columnDefinition = $table->$first();
        }
        unset($tableMethods[$first]);

        // merge then apply the remaining methods to be called on the returned ColumnDefinition instance
        $otherMethods = array_merge($tableMethods, $this->columnDefinitionsArray['column']);
        foreach ($otherMethods as $method => $argument) {
            if ($argument !== null) {
                $this->columnDefinition->$method($argument);
                continue;
            }

            $this->columnDefinition->$method();
        }

        return $this->columnDefinition;
    }

    // --------------------------------------------------
    // Validation rules

    protected $validationRules;

    protected function resolveValidationRules(): void
    {
        if (!$this->metadataNormalized) {
            $this->normalizeMetadata();
        }

        $availableRules = ['{rule}' => null];
        $assocRules = array_intersect_key($this->metadata, $availableRules);

        $rules = [];
        foreach ($assocRules as $rule => $value) {
            if ($value !== null) {
                if (is_array($value)) { // for rules like 'in'
                    $value = implode(',', $value);
                }

                $rule .= ":$value";
            }

            $rules[] = $rule;
        }

        if (isset($this->metadata['default']) && !in_array('nullable', $rules)) {
            // fields with a default value are by definition nullable
            $rules[] = 'nullable';
        }

        if (!isset($this->metadata['default']) && !isset($this->metadata['nullable']) && in_array('required', $rules)) {
            // a non-nullable field without default value is required
            $rules[] = 'required';
        }

        if (isset($this->metadata['enum']) || isset($this->metadata['set'])) {
            // the 'in' rule should exists
            $exists = false;
            foreach ($rules as $rule) {
                if (strpos($rule, 'in:') === 0) {
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $values = $this->metadata['enum'] ?? $this->metadata['set'];
                if (is_array($values)) {
                    $values = implode(',', $values);
                }
                $rules[] = "in:$values";
            }
        }

        // TODO handle instanciating custom rules
        // TODO handle exists rule with relations

        $this->validationRules['create'] = $rules;

        $this->validationRules['update'] = $rules;
        unset($this->validationRules['update']['unique']);
    }

    /**
     * @return array<string|object>
     */
    public function getCreateValidationRules(): array
    {
        if (! isset($this->validationRules['create'])) {
            $this->resolveValidationRules();
        }

        return $this->validationRules['create'];
    }

    public function getUpdateValidationRules(array $attributes = null)
    {
        if (! isset($this->validationRules['update'])) {
            $this->resolveValidationRules();
        }

        return $this->validationRules['update'];
    }

    // --------------------------------------------------
    // Casts

    /** @var null|string Will have the special value '_none' when the cast has been resolved to ... no cast */
    protected $cast;

    protected static $availableCasts = [
        // as of 6.x
        'integer' => null, 'real' => null, 'float' => null, 'double' => null, 'decimal' => null, 'string' => null,
        'boolean' => null, 'object' => null, 'array' => null, 'collection' => null,
        'date' => null, 'datetime' => null, 'timestamp' => null,

    ];

    protected static $availableCastsWithValue = [
        'decimal' => null, 'date' => null, 'datetime' => null,
        // TODO handle custom cast like this "cast:{my custom cast}"
    ];

    protected function resolveCast(): void
    {
        if ($this->cast !== null) {
            return;
        }

        $cast = array_intersect_key($this->metadata, static::$availableCasts);
        if (isset($cast[0])) {
            $this->cast = $cast[0];

            return;
        }

        // TODO handle cast with value
    }

    public function getCast(): ?string
    {
        if ($this->cast === null) {
            $this->resolveCast();
        }

        return $this->cast === '_none' ? null : $this->cast;
    }

    // --------------------------------------------------
    // Relations

    /**
     * @var null|false False when not a relation
     */
    protected $relation;

    protected function resolveRelation(): void
    {
        // relation fqcn
        $availableRelations = ['' => null];
    }

    public function isRelation(): bool
    {
        if ($this->relation === null) {
            $this->resolveRelation();
        }

        return (bool)$this->relation;
    }

    // --------------------------------------------------
    // Nova fields

    protected $novaFields;

    // --------------------------------------------------
    // Utilities
}

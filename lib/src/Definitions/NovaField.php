<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Definitions;

use Illuminate\Support\Str;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Code;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

/**
 * @mixin \Laravel\Nova\Fields\Field
 *
 * @method $this name(string $name)
 * @method $this attribute(string $attrName)
 * @method $this resource(string $resourceFqcn)
 */
class NovaField extends Fluent
{
    // An important bit to remember is that Nova may not be installed on the user's
    // project, so we must *not* use any of Nova classes here, at least outside PHPDocs.
    // That's why we use here a class extending the Fluent one, pretty much like for
    // the DB column definitions

    /**
     * @param string $type The type of the field as a shorthand or an Fqcn
     *
     * @return static
     */
    public function type(string $type)
    {
        if (strpos($type, '\\') !== false) {
            $this->attributes['type'] = $type;

            return $this;
        }

        $type = ucfirst($type);
        switch ($type) {
            case 'Id':
                $type = 'ID';
                break;
            case 'String':
                $type = 'Text';
                break;
            case 'Json':
                $type = 'Code';
                break;
            case 'Datetime':
            case 'Timestamp':
                $type = 'DateTime';
                break;
        }

        $this->attributes['type'] = '\\Laravel\\Nova\\Fields\\' . $type;

        return $this;
    }

    /**
     * @return null|\Laravel\Nova\Fields\Field
     */
    public function getInstance(): ?Field
    {
        $type = $this->get('type');
        $attribute = $this->get('attribute');
        if ($type === null || $attribute === null) {
            return null;
        }

        $name = $this->get('name');
        if ($name === null) {
            $name = Str::studly($attribute);
        }

        /** @var \Laravel\Nova\Fields\Field $field */
        $field = new $type(
            $name,
            $attribute,
            $this->get('resource') // only useful for relationship fields
        );

        $this->applyTo($field, ['type', 'name', 'attribute', 'resource']);

        return $field;
    }

    // --------------------------------------------------

    /**
     * @param string $type The Fqcn or base class name of the field
     *
     * @return static
     */
    public static function make(string $type)
    {
        return new static(['type' => $type]);
    }

    /**
     * @param array<int|string, mixed> $attributes
     *
     * @return static|\Laravel\Nova\Fields\Boolean
     */
    public static function boolean(array $attributes = [])
    {
        return new static(array_merge($attributes, [
            'type' => Boolean::class,
        ]));
    }

    /**
     * @param array<int|string, mixed> $attributes
     *
     * @return static|\Laravel\Nova\Fields\Date
     */
    public static function date(array $attributes = [])
    {
        return new static(array_merge($attributes, [
            'type' => Date::class,
            'sortable' => true,
        ]));
    }

    /**
     * @param array<int|string, mixed> $attributes
     *
     * @return static|\Laravel\Nova\Fields\DateTime
     */
    public static function datetime(array $attributes = [])
    {
        return new static(array_merge($attributes, [
            'type' => DateTime::class,
            'sortable' => true,
        ]));
    }

    /**
     * @param array<int|string, mixed> $attributes
     *
     * @return static|\Laravel\Nova\Fields\Code
     */
    public static function json(array $attributes = [])
    {
        return new static(array_merge($attributes, [
            'type' => Code::class,
            'json' => null,
        ]));
    }

    /**
     * @param array<int|string, mixed> $attributes
     *
     * @return static|\Laravel\Nova\Fields\Number
     */
    public static function number(array $attributes = [])
    {
        return new static(array_merge($attributes, [
            'type' => Number::class,
            'sortable' => true,
        ]));
    }

    /**
     * @param array<int|string, mixed> $attributes
     *
     * @return static|\Laravel\Nova\Fields\Select
     */
    public static function select(array $attributes = [])
    {
        return new static(array_merge($attributes, [
            'type' => Select::class
        ]));
    }

    /**
     * @param array<int|string, mixed> $attributes
     *
     * @return static|\Laravel\Nova\Fields\Text
     */
    public static function text(array $attributes = [])
    {
        return new static(array_merge($attributes, [
            'type' => Text::class,
            'sortable' => null,
        ]));
    }

    /**
     * @param array<int|string, mixed> $attributes
     *
     * @return static|\Laravel\Nova\Fields\Textarea
     */
    public static function textarea(array $attributes = [])
    {
        return new static(array_merge($attributes, [
            'type' => Textarea::class,
        ]));
    }
}

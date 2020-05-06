<?php

namespace FlorentPoujol\LaravelAttributePresets;

use FlorentPoujol\LaravelAttributePresets\Defaults\Boolean;
use Illuminate\Support\Fluent;
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
 */
class NovaFieldDefinition extends Fluent
{
    // An important bit to remember is that Nova may not be installed on the user's
    // project, so we must *not* use any of Nova classes here, at least outside PHPDocs.
    // That's why we use here a class extending the Fluent one, pretty much like for
    // the DB column definitions

    /**
     * @param string $type The Fqcn or base class name of the field
     *
     * @return static
     */
    public static function make(string $type)
    {
        return new static(['type' => $type]);
    }

    public function offsetSet($offset, $value): void
    {
        if ($offset === 'type') {
            if (strpos($offset, '\\') !== false) {
                $this->attributes['type'] = $offset;

                return;
            }

            $offset = ucfirst($offset);
            switch ($offset) {
                case 'Id':
                    $offset = 'ID';
                    break;
                case 'String':
                    $offset = 'Text';
                    break;
                case 'Json':
                    $offset = 'Code';
                    break;
                case 'Datetime':
                case 'Timestamp':
                    $offset = 'DateTime';
                    break;
            }

            $this->attributes['type'] = '\\Laravel\\Nova\\Fields\\' . $offset;

            return;
        }

        parent::offsetSet($offset, $value);
    }

    /**
     * @param array<string|callable> ...$constructorParams
     * @return \Laravel\Nova\Fields\Field
     */
    public function getFieldInstance(...$constructorParams): Field
    {
        $type = $this->attributes['type'] ?? null;
        if ($type === null) {
            throw new \LogicException();
        }

        /** @var \Laravel\Nova\Fields\Field $field */
        $field = new $type(...$constructorParams);

        foreach ($this->attributes as $method => $args) {
            if ($method === 'type') {
                continue;
            }

            if ($args === null) {
                $args = [];
            } elseif (! is_array($args)) {
                $args = [$args];
            }

            $field->$method(...$args);
        }

        return $field;
    }

    public function removeDefinition(string $key): self
    {
        $this->offsetUnset($key);

        return $this;
    }

    public function clear(): void
    {
        $this->attributes = [];
    }

    public function __call($method, $parameters)
    {
        $this->attributes[$method] = empty($parameters) ? null : $parameters; // allow to store multiple parameters

        return $this;
    }

    // --------------------------------------------------

    /**
     * @return static&\Laravel\Nova\Fields\Boolean
     */
    public static function boolean(array $attributes = [])
    {
        return new static(array_merge($attributes, [
            'type' => Boolean::class,
        ]));
    }

    /**
     * @return static&\Laravel\Nova\Fields\Date
     */
    public static function date(array $attributes = [])
    {
        return new static(array_merge($attributes, [
            'type' => Date::class,
        ]));
    }

    /**
     * @return static&\Laravel\Nova\Fields\DateTime
     */
    public static function datetime(array $attributes = [])
    {
        return new static(array_merge($attributes, [
            'type' => DateTime::class,
        ]));
    }

    /**
     * @return static&\Laravel\Nova\Fields\Code
     */
    public static function json(array $attributes = [])
    {
        return new static(array_merge($attributes, [
            'type' => Code::class,
            'json' => null,
        ]));
    }

    /**
     * @return static&\Laravel\Nova\Fields\Number
     */
    public static function number(array $attributes = [])
    {
        return new static(array_merge($attributes, [
            'type' => Number::class,
            'sortable' => null,
        ]));
    }

    /**
     * @return static&\Laravel\Nova\Fields\Select
     */
    public static function select(array $attributes = [])
    {
        return new static(array_merge($attributes, [
            'type' => Select::class
        ]));
    }

    /**
     * @return static&\Laravel\Nova\Fields\Text
     */
    public static function text(array $attributes = [])
    {
        return new static(array_merge($attributes, [
            'type' => Text::class,
            'sortable' => null,
        ]));
    }

    /**
     * @return static&\Laravel\Nova\Fields\Textarea
     */
    public static function textarea(array $attributes = [])
    {
        return new static(array_merge($attributes, [
            'type' => Textarea::class,
        ]));
    }
}

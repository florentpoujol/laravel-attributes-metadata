<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Definitions;

use function array_key_exists;
use function explode;
use function is_string;
use function strpos;

class Fluent extends \Illuminate\Support\Fluent
{
    /**
     * @noinspection PhpMissingParentConstructorInspection
     * @noinspection MagicMethodsValidityInspection
     */
    public function __construct(array $attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * @param array $attributes
     *
     * @return static
     */
    public function fill(array $attributes = [])
    {
        foreach ($attributes as $key => $value) {
            if (is_int($key)) {
                $this->$value();

                continue;
            }

            if (is_array($value)) {
                $this->$key(...$value);
            } else {
                $this->$key($value);
            }
            // doing it like that instead of calling offsetSet() right away
            // allow actual methods to catch the call
        }

        return $this;
    }

    /**
     * @param string $method
     * @param array $parameters
     *
     * @return mixed
     */
    public function __call($method, $parameters = [])
    {
        if (strpos($method, 'get') === 0) {
            return $this->get(lcfirst(substr($method, 3)));
        }

        if (strpos($method, 'has') === 0) {
            return $this->has(lcfirst(substr($method, 3)));
        }

        if (strpos($method, 'is') === 0) {
            return $this->is(lcfirst(substr($method, 2)));
        }

        if (count($parameters) === 1 && ! is_array($parameters[0])) {
            $parameters = $parameters[0];
        }

        return $this->offsetSet($method, $parameters);
    }

    /**
     * @return static
     */
    public function applyTo(object $instance)
    {
        foreach ($this->attributes as $method => $arguments) {
            if ($arguments === null) {
                $instance->$method();

                continue;
            }

            if (! is_array($arguments)) {
                $arguments = [$arguments];
            }

            $instance->$method(...$arguments);
        }

        return $this;
    }

    /**
     * Pass the current Fluent instance as the first argument of the provided callback
     *
     * @return static
     */
    public function tap(callable $callback)
    {
        call_user_func($callback, $this);

        return $this;
    }

    /**
     * @param int|string|object $offset
     * @param mixed $value
     *
     * @return static
     */
    public function set($key, $value = null)
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * @param string|object $value
     *
     * @return static
     */
    public function add($value)
    {
        $this->offsetSet($value);

        return $this;
    }

    /**
     * @param int|string|object $offset
     * @param mixed $value
     *
     * @return static
     */
    public function offsetSet($offset, $value = null)
    {
        if (is_int($offset)) {
            $offset = $value;
            $value = null;
        }

        if (is_string($offset)) {
            // eg: "-min"
            if (strpos('-', $offset) === 0) {
                $this->offsetUnset($offset);
                $this->offsetUnset(substr($offset, 1));

                return $this;
            }

            // eg: "min:5"
            if (strpos(':', $offset) !== false) {
                [$offset, $value] = explode(':', $offset, 2);
            }

            if (is_array($value) && empty($value)) {
                $value = null;
            }

            $this->attributes[$offset] = $value;
        }

        return $this;
    }



    /**
     * @param string|object $key
     *
     * @return static
     */
    public function remove($key)
    {
        unset($this->attributes[$key]);

        return $this;
    }

    /**
     * @return static
     */
    public function clear(array $attributes = [])
    {
        if (empty($attributes)) {
            $this->attributes = [];
        } else {
            foreach ($attributes as $value) {
                $this->remove($value);
            }
        }

        return $this;
    }

    /**
     * @param string $key
     */
    public function has($key): bool
    {
        return array_key_exists($key, $this->attributes);
    }

    /**
     * @param string $key
     *
     * @return bool Returns `true` if the key exists and isn't false
     */
    public function is($key): bool
    {
        return $this->get($key, false) !== false;
    }
}

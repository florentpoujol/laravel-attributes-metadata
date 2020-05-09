<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets;

use function array_key_exists;
use function get_class;
use function is_object;
use function is_string;
use function strpos;

class Fluent extends \Illuminate\Support\Fluent
{
    public static function make($attributes = [])
    {
        return new static($attributes);
    }

    public function __construct($attributes = [])
    {
        $this->fill($attributes);
    }

    /**
     * @param array<int|string|object, int|string|object> $attributes
     */
    public function fill($attributes = []): self
    {
        foreach ($attributes as $key => $value) {
            $this->offsetSet($key, $value);
        }

        return $this;
    }

    /**
     * @param int|string|object $offset
     * @param mixed $value
     */
    public function set($key, $value = null): self
    {
        $this->offsetSet($key, $value);

        return $this;
    }

    /**
     * @param string|object $value
     */
    public function add($value): self
    {
        $this->offsetSet($value);

        return $this;
    }

    /** @var array<string> Whitelist of the actual properties on the object that can be set from offsetSet() method (and the others that call it) */
    protected $fillableProperties = [];

    /**
     * @param int|string|object $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value = null): void
    {
        if (is_int($offset)) {
            $offset = $value;
            $value = null;
        }

        if (is_object($offset)) {
            $this->attributes[get_class($offset)] = $offset;

            return;
        }

        if (is_string($offset)) {
            // eg: "-min"
            if (strpos('-', $offset) === 0) {
                $this->offsetUnset($offset);
                $this->offsetUnset(substr($offset, 1));

                return;
            }

            if (strpos(':', $offset) !== false) {
                [$offset, $value] = explode(':', $offset, 2);

                // "min:5,2"
                if (strpos($value, ',') !== false) {
                    $value = explode(',', $value);
                }
            }

            if (property_exists($this, $offset) && in_array($offset, $this->fillableProperties)) {
                $this->$offset = $value;
            } else {
                $this->attributes[$offset] = $value;
            }
        }
    }

    public function __call($method, $parameters)
    {
        $this->offsetSet($method, empty($parameters) ? null : $parameters); // allow to store multiple parameters

        return $this;
    }

    /**
     * @param string|object $key
     */
    public function remove($key): self
    {
        if (is_object($key)) {
            $key = get_class($key);
        }

        unset($this->attributes[$key]);

        return $this;
    }

    public function clear($attributes = []): self
    {
        if (empty($attributes)) {
            $this->attributes = [];
        } else {
            foreach ($attributes as $key => $value) {
                $this->remove($key);
            }
        }

        return $this;
    }

    /**
     * @param string|object $key
     */
    public function has($key): bool
    {
        if (is_object($key)) {
            $key = get_class($key);
        }

        return array_key_exists($key, $this->attributes);
    }
}

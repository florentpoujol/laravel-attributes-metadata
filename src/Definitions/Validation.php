<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Definitions;

use function get_class;
use function is_object;
use function is_string;
use function strpos;
use function strtok;

class Validation extends Fluent
{
    /**
     * @return array<string|\Illuminate\Validation\Rule>
     */
    public function getRules(): array
    {
        return array_values($this->attributes);
    }

    /** @var null|string */
    protected $message;

    /**
     * @return static
     */
    public function message(?string $message)
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    /**
     * @param int|string|\Illuminate\Validation\Rule $offset
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

        if (is_string($offset) && strpos(':', $offset) !== false) {
            // eg: "min:5"
            $offset = strtok($offset, ':');
            // the value is still the full rule, with values
            // the keys is the rule name
        }

        if (is_array($value)) {
            $value = implode(',', $value);
        }

        if (is_string($value) && strpos($value, $offset) !== 0) {
            $value = "$offset:$value";
        }

        parent::offsetSet($offset, $value);
    }

    /**
     * @param string|\Illuminate\Validation\Rule $key
     *
     * @return static
     */
    public function remove($key)
    {
        if (is_object($key)) {
            $key = get_class($key);
        }

        parent::remove($key);

        return $this;
    }

    /**
     * @param string|\Illuminate\Validation\Rule $key
     */
    public function has($key): bool
    {
        if (is_object($key)) {
            $key = get_class($key);
        }

        return parent::has($key);
    }
}

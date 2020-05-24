<?php

declare(strict_types=1);

namespace FlorentPoujol\LaravelAttributePresets\Definitions;

use Illuminate\Support\Str;

class Validation extends Fluent
{
    /**
     * @param string $method
     * @param array<mixed> $parameters
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

        // for validation rules, the supported arguments are either
        // one argument, which can be an array
        // or a variable number of arguments
        // There is never a case where there is several argument and the first one is an array
        if (count($parameters) === 1) {
            $parameters = $parameters[0];
        }

        return $this->offsetSet($method, $parameters);
    }

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
     * @param int|string|\Illuminate\Validation\Rule $ruleName
     * @param mixed $value
     */
    public function offsetSet($ruleName, $value = null)
    {
        if (is_int($ruleName)) {
            $ruleName = $value;
        } elseif (is_object($ruleName)) {
            $this->attributes[get_class($ruleName)] = $ruleName;

            return $this;
        }

        if ($value === null || (is_array($value) && empty($value))) {
            // $ruleName is like 'required' (or 'min:5' when filled from array)
            $value = $ruleName;
        }

        if (is_string($ruleName) && Str::contains($ruleName, ':')) {
            // eg: "min:5"
            $ruleName = strtok($ruleName, ':');
            // the value is still the full rule, with values
            // the key is the rule name
        }

        if (is_array($value)) {
            $value = implode(',', $value);
        }

        if (! is_object($value) && ! Str::contains($value, $ruleName)) {
            // if the rule value does not contain the rule name, add it
            $value = "$ruleName:$value";
        }

        return parent::offsetSet($ruleName, $value);
    }

    /**
     * @param string|\Illuminate\Validation\Rule $key
     *
     * @return static
     */
    public function remove($key)
    {
        return parent::remove($key);
    }

    /**
     * @param string|\Illuminate\Validation\Rule $key
     */
    public function has($key): bool
    {
        return parent::has($key);
    }
}

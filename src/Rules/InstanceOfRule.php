<?php

namespace Matteoc99\LaravelPreference\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InstanceOfRule implements ValidationRule
{
    public function __construct(protected string $instance)
    {
    }

    public function message(): string
    {
        return sprintf('%s must be implemented', $this->instance);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_object($value) && ! is_string($value)) {
            $fail($this->message());

            return;
        }

        $className = is_object($value) ? get_class($value) : $value;

        if (! class_exists($className)) {
            $fail($this->message());

            return;
        }
        if ($className == $this->instance) {
            return; // success
        }

        if (! in_array($this->instance, class_implements($className))) {
            $fail($this->message());
        }
    }
}

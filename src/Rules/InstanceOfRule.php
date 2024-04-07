<?php

namespace Matteoc99\LaravelPreference\Rules;


use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InstanceOfRule implements ValidationRule
{

    public function __construct(protected string $instance) { }

    public function passes($attribute, $value): bool
    {
        if (!is_string($value)) $value = $value::class;
        if (!class_exists($value)) return false;

        return in_array($this->instance, class_implements($value));
    }

    public function message(): string
    {
        return sprintf("%s must be implemented", $this->instance);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->passes($attribute, $value)) {
            $fail($this->message());
        }
    }
}
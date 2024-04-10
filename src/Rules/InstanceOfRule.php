<?php

namespace Matteoc99\LaravelPreference\Rules;


use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InstanceOfRule implements ValidationRule
{

    public function __construct(protected string $instance) { }


    public function message(): string
    {
        return sprintf("%s must be implemented", $this->instance);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value)) $value = $value::class;
        if (!class_exists($value)) {
            $fail($this->message());
            return;
        }

        if (!in_array($this->instance, class_implements($value))) {
            $fail($this->message());
        }
    }
}
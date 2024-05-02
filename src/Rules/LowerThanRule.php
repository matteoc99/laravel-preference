<?php

namespace Matteoc99\LaravelPreference\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LowerThanRule implements ValidationRule
{
    public function __construct(protected float $value) { }

    public function message()
    {
        return sprintf("A value lower than '%d' expected", $this->value);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!(is_int($value) || is_float($value)) || $value > $this->value) {
            $fail($this->message());
        }
    }
}
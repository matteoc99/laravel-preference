<?php

namespace Matteoc99\LaravelPreference\Tests\TestSubjects\Models;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LowerThanRule implements ValidationRule
{
    public function __construct(protected int $value) { }

    public function passes($attribute, $value)
    {
        return is_int($value) && $value < $this->value;
    }

    public function message()
    {
        return sprintf("A value lower than  '%d' expected", $this->value);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->passes($attribute, $value)) {
            $fail($this->message());
        }
    }
}
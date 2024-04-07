<?php

namespace Matteoc99\LaravelPreference\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Matteoc99\LaravelPreference\Enums\Type;

class IsRule implements ValidationRule
{
    public function __construct(protected Type $type)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $isValid = match ($this->type) {
            Type::BOOL => is_bool($value),
            Type::INT => is_int($value),
            Type::FLOAT => is_float($value),
            Type::STRING => is_string($value),
            Type::ARRAY => is_array($value),
            Type::OBJECT => is_object($value),
            Type::CALLABLE => is_callable($value),
            Type::ITERABLE => is_iterable($value),
            Type::NULL => is_null($value),
            Type::RESOURCE => is_resource($value),
        };

        if (!$isValid) {
            $fail(sprintf('The %s must be of type %s.', $attribute, $this->type->name));
        }
    }
}
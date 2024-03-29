<?php

namespace Matteoc99\LaravelPreference\Rules;


use Closure;

class InstanceOfRule extends DataRule
{

    public function passes($attribute, $value): bool
    {
        if (!is_string($value)) $value = $value::class;
        if (!class_exists($value)) return false;

        $instances = class_implements($value);
        foreach ($this->getData() as $instance) {
            if (!in_array($instance, $instances)) {
                return false;
            }
        }
        return !empty($this->getData());
    }

    public function message(): string
    {
        return sprintf("One of: %s must be implemented", implode(", ", $this->getData()));
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->passes($attribute, $value)) {
            $fail($this->message());
        }
    }
}
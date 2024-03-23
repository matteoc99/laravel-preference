<?php

namespace Matteoc99\LaravelPreference\Rules;


class InstanceOfRule extends DataRule
{

    public function passes($attribute, $value)
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

    public function message()
    {
        return sprintf("One of: %s must be implemented", implode(", ", $this->getData()));
    }
}
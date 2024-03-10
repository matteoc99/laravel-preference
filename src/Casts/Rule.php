<?php

namespace Matteoc99\LaravelPreference\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\App;
use Matteoc99\LaravelPreference\Contracts\HasValidation;
use Matteoc99\LaravelPreference\Rules\DataRule;

class Rule implements CastsAttributes
{


    public function get($model, string $key, mixed $value, array $attributes): HasValidation
    {
        return $this->deserialize($attributes);
    }

    protected function deserialize($value)
    {
        if (empty($value)) {
            return null;
        }

        $class = $value['class'];

        if (!class_exists($class)) {
            throw new \InvalidArgumentException("Enum class $class does not exist.");
        }

        /**@var HasValidation $rule * */
        $rule = App::make($class);

        if ($rule instanceof DataRule) {
            $rule->setData($value['data'] ?? []);
        }

        return $rule;
    }

    public function set($model, string $key, mixed $value, array $attributes): array
    {
        return $this->serialize($value);
    }

    protected function serialize($rule): array
    {
        if (!$rule instanceof HasValidation) {
            throw new \InvalidArgumentException("Invalid value for HasValidation attribute.");
        }

        $resp = [
            'class' => get_class($rule),
        ];

        if ($rule instanceof DataRule) {
            $resp['data'] = $rule->getData();
        }

        return $resp;
    }
}
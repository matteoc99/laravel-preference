<?php

namespace Matteoc99\LaravelPreference\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Matteoc99\LaravelPreference\Contracts\HasValidation;
use Matteoc99\LaravelPreference\Rules\DataRule;

class RuleCaster implements CastsAttributes
{
    public function get(?Model $model, string $key, mixed $value, array $attributes)
    {
        return $this->deserializerRule($value);
    }

    protected function deserializerRule($value)
    {
        if (empty($value)) {
            return null;
        }
        $value = json_decode($value, true);

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

    public function set(?Model $model, string $key, mixed $value, array $attributes)
    {
        return json_encode($this->serializeRule($value));
    }

    protected function serializeRule($rule): array
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
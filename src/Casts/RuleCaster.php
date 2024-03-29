<?php

namespace Matteoc99\LaravelPreference\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use InvalidArgumentException;
use Matteoc99\LaravelPreference\Rules\DataRule;

class RuleCaster implements CastsAttributes
{
    public function get(?Model $model, string $key, mixed $value, array $attributes): DataRule|ValidationRule|null
    {
        return $this->deserializerRule($value);
    }

    protected function deserializerRule($value): DataRule|ValidationRule|null
    {
        if (empty($value)) {
            return null;
        }
        $value = json_decode($value, true);

        $class = $value['class'];

        if (!class_exists($class)) {
            throw new InvalidArgumentException("Enum class $class does not exist.");
        }

        /**@var ValidationRule $rule * */
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
        if (!$rule instanceof ValidationRule) {
            throw new InvalidArgumentException("Invalid value for ValidationRule attribute.");
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
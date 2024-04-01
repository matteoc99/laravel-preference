<?php

namespace Matteoc99\LaravelPreference\Models;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Matteoc99\LaravelPreference\Casts\EnumCaster;
use Matteoc99\LaravelPreference\Casts\RuleCaster;
use Matteoc99\LaravelPreference\Casts\ValueCaster;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;


/**
 * Class Preference
 *
 * @package Matteoc99\LaravelPreference\Models
 * @property int                 $id
 * @property string              $group
 * @property string              $name
 * @property string|null         $description
 * @property CastableEnum|null   $cast
 * @property ValidationRule|null $rule
 * @property mixed               $default_value
 * @property Carbon              $created_at
 * @property Carbon              $updated_at
 */
class Preference extends BaseModel
{

    protected $table = "preferences";

    protected $fillable = [
        'group',
        'name',
        'description',
        'cast',
        'rule',
        'default_value',
    ];

    protected $casts = [
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'cast'          => EnumCaster::class,
        'rule'          => RuleCaster::class,
        'default_value' => ValueCaster::class,
    ];

    public function getValidationRules(): array
    {
        $rules = [];
        if ($this->cast) {
            $castValidation = $this->cast->validation();
            if ($castValidation) {
                $rules = array_merge($rules, $this->processRule($castValidation));
            }
        }
        if ($this->rule) {
            $rules = array_merge($rules, $this->processRule($this->rule));
        }

        return $rules;
    }
    private function processRule($rule): array
    {
        if (is_array($rule)) {
            return $rule;
        } elseif ($rule instanceof ValidationRule) {
            return [$rule];
        } else {
            return explode('|', $rule);
        }
    }

}
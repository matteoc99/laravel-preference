<?php

namespace Matteoc99\LaravelPreference\Models;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Carbon;
use Matteoc99\LaravelPreference\Casts\EnumCaster;
use Matteoc99\LaravelPreference\Casts\RuleCaster;
use Matteoc99\LaravelPreference\Casts\ValueCaster;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;
use Matteoc99\LaravelPreference\Contracts\HasValidation;


/**
 * Class Preference
 *
 * @package Matteoc99\LaravelPreference\Models
 * @property int                $id
 * @property string             $group
 * @property string             $name
 * @property string|null        $description
 * @property CastableEnum|null  $cast
 * @property HasValidation|null $rule
 * @property mixed              $default_value
 * @property Carbon             $created_at
 * @property Carbon             $updated_at
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
        $rule = $this->cast?->validation();
        if (empty($rule)) return [$this?->rule];

        if (!is_array($rule)) {
            if ($rule instanceof Rule) {
                $rule = [$rule];
            } else {
                $rule = explode('|', $rule);
            }
        }

        return array_merge($rule, [$this?->rule]);
    }

}
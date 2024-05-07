<?php

namespace Matteoc99\LaravelPreference\Models;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
use Matteoc99\LaravelPreference\Casts\SerializingCaster;
use Matteoc99\LaravelPreference\Casts\ValueCaster;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;
use Matteoc99\LaravelPreference\Contracts\PreferencePolicy;

/**
 * Class Preference
 *
 * @property int $id
 * @property string $group
 * @property string $name
 * @property string|null $description
 * @property CastableEnum|null $cast
 * @property ValidationRule|null $rule
 * @property PreferencePolicy|null $policy
 * @property mixed $default_value
 * @property string[] $allowed_values
 * @property bool $nullable
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Preference extends BaseModel
{
    protected $table = 'preferences';

    protected $fillable = [
        'group',
        'name',
        'description',
        'cast',
        'policy',
        'nullable',
        'rule',
        'default_value',
        'allowed_values',
    ];

    protected $attributes = [
        'nullable' => false,
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'cast' => SerializingCaster::class,
        'rule' => SerializingCaster::class,
        'policy' => SerializingCaster::class,
        'default_value' => ValueCaster::class,
        'allowed_values' => 'array',
        'nullable' => 'boolean',
    ];

    public function attributesToArray()
    {

        $attributes = parent::attributesToArray();

        return array_merge(
            $attributes,
            [
                'default_value' => $this->default_value,
                'policy' => $this->policy,
                'rule' => $this->rule,
                'cast' => $this->cast,
            ],
        );
    }
}

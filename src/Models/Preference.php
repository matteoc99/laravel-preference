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
 * @package Matteoc99\LaravelPreference\Models
 * @property int                   $id
 * @property string                $group
 * @property string                $name
 * @property string|null           $description
 * @property CastableEnum|null     $cast
 * @property ValidationRule|null   $rule
 * @property PreferencePolicy|null $policy
 * @property mixed                 $default_value
 * @property Carbon                $created_at
 * @property Carbon                $updated_at
 */
class Preference extends BaseModel
{

    protected $table = "preferences";

    protected $fillable = [
        'group',
        'name',
        'description',
        'cast',
        'policy',
        'rule',
        'default_value',
    ];

    protected $casts = [
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
        'cast'          => SerializingCaster::class,
        'rule'          => SerializingCaster::class,
        'policy'        => SerializingCaster::class,
        'default_value' => ValueCaster::class,
    ];

}
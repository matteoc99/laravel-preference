<?php

namespace Matteoc99\LaravelPreference\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Matteoc99\LaravelPreference\Casts\Enum;
use Matteoc99\LaravelPreference\Casts\Rule;
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
 * @property CastableEnum       $cast
 * @property HasValidation|null $rule
 * @property Carbon             $created_at
 * @property Carbon             $updated_at
 */
class Preference extends Model
{

    protected $table = "preferences";

    protected $fillable = [
        'group',
        'name',
        'description',
        'cast',
        'rule',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'cast'       => Enum::class,
        'rule'       => Rule::class,
    ];

}
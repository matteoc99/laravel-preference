<?php

namespace Matteoc99\LaravelPreference\Tests\TestSubjects\Models;

use Matteoc99\LaravelPreference\Contracts\PreferenceableModel;
use Matteoc99\LaravelPreference\Traits\HasPreferences;

class User extends \Illuminate\Foundation\Auth\User implements PreferenceableModel
{
    use HasPreferences;

    protected $fillable = ['email'];
}
<?php

namespace Matteoc99\LaravelPreference\Tests\Models;

use Matteoc99\LaravelPreference\Traits\HasPreferences;

class User extends \Illuminate\Foundation\Auth\User
{
    use HasPreferences;

    protected $fillable = ['email'];
}
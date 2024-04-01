<?php

namespace Matteoc99\LaravelPreference\Tests\TestSubjects\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Matteoc99\LaravelPreference\Contracts\PreferenceableModel;
use Matteoc99\LaravelPreference\Enums\PolicyAction;
use Matteoc99\LaravelPreference\Traits\HasPreferences;

class User extends \Illuminate\Foundation\Auth\User implements PreferenceableModel
{
    use HasPreferences;

    protected $fillable = ['email'];

    public function isUserAuthorized(?Authenticatable $user, PolicyAction $action): bool
    {
        return $user?->id == $this->id ;
    }
}
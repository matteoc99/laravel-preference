<?php

namespace Matteoc99\LaravelPreference\Tests\TestSubjects\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;
use Matteoc99\LaravelPreference\Contracts\PreferenceableModel;
use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;
use Matteoc99\LaravelPreference\Contracts\PreferencePolicy;

class MyPolicy implements PreferencePolicy
{
    use HandlesAuthorization;

    public function index(?Authenticatable $user, PreferenceableModel $model, PreferenceGroup $preference): bool
    {
        return $this->isAuthorized($user, $model);
    }

    public function get(?Authenticatable $user, PreferenceableModel $model, PreferenceGroup $preference): bool
    {
        return $this->isAuthorized($user, $model);
    }

    public function update(?Authenticatable $user, PreferenceableModel $model, PreferenceGroup $preference): bool
    {
        return $this->isAuthorized($user, $model);
    }

    public function delete(?Authenticatable $user, PreferenceableModel $model, PreferenceGroup $preference): bool
    {
        return false;
    }

    protected function isAuthorized(?Authenticatable $user, PreferenceableModel $model): bool
    {

        if (! $user) {
            return false;
        }

        if ($user->admin) {
            return true;
        }

        return $user->getAuthIdentifier() == $model->id;

    }
}

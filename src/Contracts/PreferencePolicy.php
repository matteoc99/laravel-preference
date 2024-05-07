<?php

namespace Matteoc99\LaravelPreference\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;

interface PreferencePolicy
{
    public function index(?Authenticatable $user, PreferenceableModel $model, PreferenceGroup $preference): bool;

    public function get(?Authenticatable $user, PreferenceableModel $model, PreferenceGroup $preference): bool;

    public function update(?Authenticatable $user, PreferenceableModel $model, PreferenceGroup $preference): bool;

    public function delete(?Authenticatable $user, PreferenceableModel $model, PreferenceGroup $preference): bool;
}

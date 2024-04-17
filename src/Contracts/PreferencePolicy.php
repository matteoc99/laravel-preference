<?php

namespace Matteoc99\LaravelPreference\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Matteoc99\LaravelPreference\Models\Preference;

interface PreferencePolicy
{
    public function index(Authenticatable $user, string $preferences): bool;

    public function get(Authenticatable $user, Preference $preference, mixed $value): bool;

    public function update(Authenticatable $user, Preference $preference, mixed $value): bool;

    public function delete(Authenticatable $user, Preference $preference, mixed $value): bool;
}
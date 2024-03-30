<?php

namespace Matteoc99\LaravelPreference\Contracts;

use Illuminate\Support\Collection;

interface PreferenceableModel
{
    public function getPreferences(string $group = null): Collection;
    public function removePreference(PreferenceGroup|string $name, string $group = null): int;
    public function setPreference(PreferenceGroup|string $name, mixed $value, string $group = null): void;
    public function getPreference(PreferenceGroup|string $name, mixed $default = null, string $group = null): mixed;

}
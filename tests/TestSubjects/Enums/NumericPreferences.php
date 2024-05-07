<?php

namespace Matteoc99\LaravelPreference\Tests\TestSubjects\Enums;

use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;

enum NumericPreferences: int implements PreferenceGroup
{
    case ONE = 1;
    case TWO = 2;
    case THREE = 3;
}

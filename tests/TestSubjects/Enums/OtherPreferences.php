<?php

namespace Matteoc99\LaravelPreference\Tests\TestSubjects\Enums;

use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;

enum OtherPreferences :string implements PreferenceGroup
{
    case LANGUAGE = "language";
    case QUALITY = "quality";
    case CONFIG = "config";
}
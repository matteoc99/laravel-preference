<?php

namespace Matteoc99\LaravelPreference\Tests\TestSubjects\Enums;

use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;

enum VideoPreferences: string implements PreferenceGroup
{
    case LANGUAGE = "language";
    case QUALITY = "quality";
    case CONFIG = "configuration";
}
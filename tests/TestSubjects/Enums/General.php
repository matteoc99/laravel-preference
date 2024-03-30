<?php

namespace Matteoc99\LaravelPreference\Tests\TestSubjects\Enums;

use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;

enum General: string implements PreferenceGroup
{
    case LANGUAGE = "language";
    case QUALITY = "quality";
    case CONFIG = "configuration";
    case VOLUME = "volume";
    case EMAILS = "emails";
    case BIRTHDAY = "birthday";
    case TIMEZONE = "timezone";
    case OPTIONS = "options";
}
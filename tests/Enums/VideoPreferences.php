<?php

namespace Matteoc99\LaravelPreference\Tests\Enums;

enum VideoPreferences :string
{
    case LANGUAGE = "language";
    case QUALITY = "quality";
    case CONFIG = "configuration";
}
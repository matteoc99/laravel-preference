<?php

namespace Matteoc99\LaravelPreference\Tests\Casts;

use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Tests\Enums\General;
use Matteoc99\LaravelPreference\Tests\TestCase;

class CasterTestCase extends TestCase
{

    protected Preference $dummyPref;

    public function setUp(): void
    {
        parent::setUp();

        $this->dummyPref = PreferenceBuilder::init(General::LANGUAGE)->create();
    }


}
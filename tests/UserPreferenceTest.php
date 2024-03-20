<?php

namespace Matteoc99\LaravelPreference\Tests;

use App\Models\User;
use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Models\UserPreference;

class UserPreferenceTest extends TestCase
{


    protected Preference $dummyPref;

    public function setUp(): void
    {
        parent::setUp();

        $this->dummyPref = PreferenceBuilder::init('test',Cast::ARRAY)
            ->create();
    }



    public function test_preferenceable_relationship()
    {
        // 1. Setup: Create necessary models for the polymorphic relationship
        // Example if preferenceable() morphs to a User model

        $this->testUser->setPreference('test',['test'=>"works"]);

        $userPreference = $this->testUser->getPreferences()->first();

        $this->assertEquals($this->dummyPref->id, $userPreference->preference->id);
        $this->assertTrue($userPreference->preferenceable()->is($this->testUser));
        $this->assertEquals("works", $userPreference->value['test']);
    }
}
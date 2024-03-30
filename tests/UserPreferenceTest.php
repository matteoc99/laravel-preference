<?php

namespace Matteoc99\LaravelPreference\Tests;

use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\General;

class UserPreferenceTest extends TestCase
{


    protected Preference $dummyPref;

    public function setUp(): void
    {
        parent::setUp();

        $this->dummyPref = PreferenceBuilder::init(General::OPTIONS,Cast::ARRAY)
            ->create();
    }


    /** @test */
    public function test_preferenceable_relationship()
    {
        $this->testUser->setPreference(General::OPTIONS,['test'=>"works"]);

        $userPreference = $this->testUser->getPreferences()->first();

        $this->assertEquals($this->dummyPref->id, $userPreference->preference->id);
        $this->assertTrue($userPreference->preferenceable()->is($this->testUser));
        $this->assertEquals("works", $userPreference->value['test']);
    }
}
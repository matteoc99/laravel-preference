<?php

namespace Matteoc99\LaravelPreference\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;

class PreferenceGroupsTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        for ($i = 0; $i < 5; $i++) {
            PreferenceBuilder::init("language")
                ->withDefaultValue("en")
                ->withGroup("g$i")
                ->create();
        }

    }


    /** @test */
    public function group_is_distinct()
    {
        $this->testUser->setPreference('language', 'de', "g1");
        $this->testUser->setPreference('language', 'it', "g2");
        $this->testUser->setPreference('language', 'fr', "g3");

        $preference = $this->testUser->getPreferences('g1');

        $this->assertEquals(1, $preference->count());
        $this->assertEquals(3, $this->testUser->getPreferences()->count());
        $this->assertEquals(0, $this->testUser->removePreference('language'));
        $this->assertEquals(1, $this->testUser->removePreference('language', 'g1'));
        $this->assertEquals(2, $this->testUser->getPreferences()->count());

    }

    /** @test */
    public function update_or_create()
    {
        for ($i = 0; $i < 5; $i++) {
            PreferenceBuilder::init("language")
                ->withDefaultValue("en")
                ->withGroup("g$i")
                ->updateOrCreate();
        }

        $this->assertDatabaseCount('preferences', 5);

    }

    public function all_preferences_in_a_group_can_be_deleted()
    {

        $this->testUser->setPreference('language', 'fr', "g4");

        $this->assertNotEmpty($this->testUser->getPreferences('g4'));
        $this->assertEquals(1, $this->testUser->removePreference('language', 'g4'));
        $this->assertEmpty($this->testUser->getPreferences('g4'));
    }

}
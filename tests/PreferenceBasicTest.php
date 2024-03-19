<?php

namespace Matteoc99\LaravelPreference\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Rules\InRule;

class PreferenceBasicTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        PreferenceBuilder::init("language")
            ->withDefaultValue("en")
            ->withRule(new InRule("en", "it", "de"))
            ->create();

    }

    public function tearDown(): void
    {
        PreferenceBuilder::init("language")
            ->delete();

        parent::tearDown();
    }


    /** @test */
    public function set_and_get_preference()
    {
        $this->testUser->setPreference('language', 'de');

        $preference = $this->testUser->getPreference('language');

        $this->assertEquals('de', $preference);
    }

    /** @test */
    public function remove_preference()
    {
        // Set a preference
        $this->testUser->setPreference('language', 'it');

        $preference = $this->testUser->getPreference('language');

        $this->assertEquals('it', $preference);

        $this->testUser->removePreference('language');

        $preference = $this->testUser->getPreference('language');

        $this->assertEquals('en', $preference);
    }

    // Add more tests for setting, removing, and getting preferences with different scenarios

    /** @test */
    public function preference_validation_rule()
    {
        $this->expectException(ValidationException::class);

        // Try to set an invalid preference
        $this->testUser->setPreference('language', 'fr');
    }

    /** @test */
    public function preference_validation_cast()
    {
        $this->expectException(ValidationException::class);

        // Try to set an invalid preference
        $this->testUser->setPreference('language', 2);
    }


}
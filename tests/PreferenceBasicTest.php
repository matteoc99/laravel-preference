<?php

namespace Matteoc99\LaravelPreference\Tests;

use Illuminate\Validation\ValidationException;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Rules\InRule;
use Matteoc99\LaravelPreference\Tests\Enums\General;
use Matteoc99\LaravelPreference\Tests\Enums\OtherPreferences;
use Matteoc99\LaravelPreference\Tests\Enums\VideoPreferences;

class PreferenceBasicTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();

        PreferenceBuilder::init(General::LANGUAGE)
            ->withDefaultValue("en")
            ->withRule(new InRule("en", "it", "de"))
            ->create();
    }

    public function tearDown(): void
    {
        PreferenceBuilder::delete(General::LANGUAGE);

        parent::tearDown();
    }


    /** @test */
    public function set_and_get_preference()
    {
        $this->testUser->setPreference(General::LANGUAGE, 'de');

        $preference = $this->testUser->getPreference(General::LANGUAGE);

        $this->assertEquals('de', $preference);
    }

    /** @test */
    public function remove_preference()
    {
        // Set a preference
        $this->testUser->setPreference(General::LANGUAGE, 'it');

        $preference = $this->testUser->getPreference(General::LANGUAGE);

        $this->assertEquals('it', $preference);

        $this->testUser->removePreference(General::LANGUAGE);

        $preference = $this->testUser->getPreference(General::LANGUAGE);

        $this->assertEquals('en', $preference);
    }

    // Add more tests for setting, removing, and getting preferences with different scenarios

    /** @test */
    public function preference_validation_rule()
    {
        $this->expectException(ValidationException::class);

        // Try to set an invalid preference
        $this->testUser->setPreference(General::LANGUAGE, 'fr');
    }

    /** @test */
    public function preference_validation_cast()
    {
        $this->expectException(ValidationException::class);

        // Try to set an invalid preference
        $this->testUser->setPreference(General::LANGUAGE, 2);
    }

    /** @test */
    public function init_and_delete()
    {
        PreferenceBuilder::init(VideoPreferences::QUALITY)
            ->withDescription("video quality")
            ->create();
        PreferenceBuilder::init(OtherPreferences::QUALITY)
            ->withDescription("video quality")
            ->create();

        $this->testUser->setPreference(VideoPreferences::QUALITY, "144p");

        PreferenceBuilder::delete(OtherPreferences::QUALITY);

        $this->assertEquals('144p', $this->testUser->getPreference(VideoPreferences::QUALITY));

    }


}
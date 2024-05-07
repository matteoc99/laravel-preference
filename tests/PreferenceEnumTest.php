<?php

namespace Matteoc99\LaravelPreference\Tests;

use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\General;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\OtherPreferences;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\VideoPreferences;

class PreferenceEnumTest extends TestCase
{
    /** @test */
    public function create_enum_preferences()
    {
        PreferenceBuilder::init(General::CONFIG)->create();
        PreferenceBuilder::init(General::LANGUAGE)->create();
        PreferenceBuilder::init(VideoPreferences::LANGUAGE)->create();
        PreferenceBuilder::init(OtherPreferences::LANGUAGE)->create();

        $this->testUser->setPreference(General::LANGUAGE, 'de');

        $this->assertEquals('default', $this->testUser->getPreference(VideoPreferences::LANGUAGE, 'default'));

        $this->testUser->setPreference(VideoPreferences::LANGUAGE, 'de');
        $this->assertEquals('de', $this->testUser->getPreference(VideoPreferences::LANGUAGE, 'default'));

        $this->assertEquals(1, $this->testUser->getPreferences(VideoPreferences::class)->count());
        $this->assertEquals(2, Preference::where('group', General::class)->count());

        $this->assertEquals('de', $this->testUser->getPreference(General::LANGUAGE, 'default'));

        $this->testUser->removePreference(General::LANGUAGE);
        $this->assertEquals('default', $this->testUser->getPreference(General::LANGUAGE, 'default'));

        $this->assertEquals(1, PreferenceBuilder::delete(General::CONFIG));
        $this->assertEquals(1, PreferenceBuilder::delete(General::LANGUAGE));

        $this->assertEquals(1, Preference::where('group', VideoPreferences::class)->count());

        $this->assertEquals(1, $this->testUser->getPreferences()->count());

        $this->assertEquals(1, PreferenceBuilder::delete(OtherPreferences::LANGUAGE));
        $this->assertEquals(1, PreferenceBuilder::delete(VideoPreferences::LANGUAGE));

        $this->assertEquals(0, Preference::all()->count());
        $this->assertEquals(0, $this->testUser->getPreferences()->count());

    }

    /** @test */
    public function create_enum_preferences_bulk()
    {
        PreferenceBuilder::initBulk([
            ['name' => General::CONFIG],
            ['name' => General::LANGUAGE],
            ['name' => VideoPreferences::LANGUAGE],
        ]);

        $deletePreferences = [
            ['name' => General::LANGUAGE],
            ['name' => General::CONFIG],
        ];
        $this->testUser->setPreference(General::LANGUAGE, 'de');

        $this->assertEquals('default', $this->testUser->getPreference(VideoPreferences::LANGUAGE, 'default'));

        $this->testUser->setPreference(VideoPreferences::LANGUAGE, 'de');
        $this->assertEquals('de', $this->testUser->getPreference(VideoPreferences::LANGUAGE, 'default'));

        $this->assertEquals(1, $this->testUser->getPreferences(VideoPreferences::class)->count());
        $this->assertEquals(2, Preference::where('group', General::class)->count());

        $this->assertEquals('de', $this->testUser->getPreference(General::LANGUAGE, 'default'));

        $this->testUser->removePreference(General::LANGUAGE);
        $this->assertEquals('default', $this->testUser->getPreference(General::LANGUAGE, 'default'));

        PreferenceBuilder::deleteBulk($deletePreferences);
        $this->assertEquals(1, Preference::all()->count());
        $this->assertEquals(1, $this->testUser->getPreferences()->count());
    }
}

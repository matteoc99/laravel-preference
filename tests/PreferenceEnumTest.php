<?php

namespace Matteoc99\LaravelPreference\Tests;

use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Tests\Enums\OtherPreferences;
use Matteoc99\LaravelPreference\Tests\Enums\Preferences;
use Matteoc99\LaravelPreference\Tests\Enums\VideoPreferences;

class PreferenceEnumTest extends TestCase
{


    /** @test */
    public function create_enum_preferences()
    {
        PreferenceBuilder::init(Preferences::CONFIG)->create();
        PreferenceBuilder::init(Preferences::LANGUAGE)->create();
        PreferenceBuilder::init(VideoPreferences::LANGUAGE)->create();
        PreferenceBuilder::init(OtherPreferences::LANGUAGE)->create();

        $this->testUser->setPreference(Preferences::LANGUAGE, 'de');

        $this->assertEquals('default', $this->testUser->getPreference(VideoPreferences::LANGUAGE, null, 'default'));

        $this->testUser->setPreference(VideoPreferences::LANGUAGE, 'de');
        $this->assertEquals('de', $this->testUser->getPreference(VideoPreferences::LANGUAGE, null, 'default'));

        $this->assertEquals(1, $this->testUser->getPreferences(VideoPreferences::class)->count());
        $this->assertEquals(2, Preference::where('group', Preferences::class)->count());

        $this->assertEquals('de', $this->testUser->getPreference(Preferences::LANGUAGE, null, 'default'));

        $this->testUser->removePreference(Preferences::LANGUAGE);
        $this->assertEquals('default', $this->testUser->getPreference(Preferences::LANGUAGE, null, 'default'));

        $this->assertEquals(1, PreferenceBuilder::delete(Preferences::CONFIG));
        $this->assertEquals(1, PreferenceBuilder::delete(Preferences::LANGUAGE));

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
            ['name' => Preferences::CONFIG],
            ['name' => Preferences::LANGUAGE],
            ['name' => VideoPreferences::LANGUAGE],
        ]);

        $deletePreferences = [
            ['name' => Preferences::LANGUAGE],
            ['name' => Preferences::CONFIG]
        ];
        $this->testUser->setPreference(Preferences::LANGUAGE, 'de');

        $this->assertEquals('default', $this->testUser->getPreference(VideoPreferences::LANGUAGE, null, 'default'));

        $this->testUser->setPreference(VideoPreferences::LANGUAGE, 'de');
        $this->assertEquals('de', $this->testUser->getPreference(VideoPreferences::LANGUAGE, null, 'default'));

        $this->assertEquals(1, $this->testUser->getPreferences(VideoPreferences::class)->count());
        $this->assertEquals(2, Preference::where('group', Preferences::class)->count());

        $this->assertEquals('de', $this->testUser->getPreference(Preferences::LANGUAGE, null, 'default'));

        $this->testUser->removePreference(Preferences::LANGUAGE);
        $this->assertEquals('default', $this->testUser->getPreference(Preferences::LANGUAGE, null, 'default'));


        PreferenceBuilder::deleteBulk($deletePreferences);
        $this->assertEquals(1, Preference::all()->count());
        $this->assertEquals(1, $this->testUser->getPreferences()->count());
    }
}

<?php

namespace Matteoc99\LaravelPreference\Tests;

use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\General;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\OtherPreferences;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\VideoPreferences;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Models\LowerThanRule;

class PreferenceBuilderBulkTest extends TestCase
{


    /** @test */
    public function init_bulk_throws_exception_with_empty_preferences_array()
    {
        $this->expectException(\InvalidArgumentException::class);

        PreferenceBuilder::initBulk([]);
    }

    /** @test */
    public function init_bulk_throws_exception_if_preference_name_is_missing()
    {
        $preferences = [
            ['cast' => Cast::STRING], // 'name' is missing
            ['name' => VideoPreferences::LANGUAGE, 'cast' => Cast::INT],
        ];

        $this->expectException(\InvalidArgumentException::class);
        PreferenceBuilder::initBulk($preferences);
    }


    /** @test */
    public function init_bulk_correctly_creates_multiple_preferences()
    {
        $preferences = [
            ['name' => General::LANGUAGE, 'cast' => Cast::STRING],
            ['name' => VideoPreferences::LANGUAGE, 'cast' => Cast::INT],
        ];

        PreferenceBuilder::initBulk($preferences);

        $this->assertDatabaseCount((new Preference())->getTable(), 2);
        $this->assertDatabaseHas((new Preference())->getTable(), ['name' => General::LANGUAGE]);
        $this->assertDatabaseHas((new Preference())->getTable(), ['name' => VideoPreferences::LANGUAGE]);
    }

    /** @test */
    public function delete_bulk_deletes_correct_preferences()
    {
        PreferenceBuilder::initBulk([
            ['name' => OtherPreferences::CONFIG, 'cast' => Cast::STRING],
            ['name' => OtherPreferences::QUALITY, 'cast' => Cast::INT],
            ['name' => VideoPreferences::LANGUAGE, 'cast' => Cast::BOOL],
        ]);

        $deletePreferences = [
            ['name' => OtherPreferences::CONFIG],
            ['name' => OtherPreferences::QUALITY]
        ];

        PreferenceBuilder::deleteBulk($deletePreferences);

        $this->assertDatabaseCount((new Preference())->getTable(), 1);
        $this->assertDatabaseHas((new Preference())->getTable(), ['name' =>  VideoPreferences::LANGUAGE]);
    }

    /** @test */
    public function init_bulk_throws_exception_with_invalid_cast()
    {
        $preferences = [
            ['name' => General::LANGUAGE, 'cast' => 'invalid_cast']
        ];

        $this->expectException(\InvalidArgumentException::class);
        PreferenceBuilder::initBulk($preferences);
    }

    /** @test */
    public function init_bulk_throws_exception_if_rule_validation_fails()
    {
        $preferences = [
            ['name' => General::LANGUAGE, 'cast' => Cast::INT, 'default_value' => 10, 'rule' => new LowerThanRule(5)]
        ];

        $this->expectException(\InvalidArgumentException::class);
        PreferenceBuilder::initBulk($preferences);
    }

    /** @test */
    public function init_bulk_creates_new_and_updates_existing_preferences()
    {
        PreferenceBuilder::init(General::LANGUAGE)->create();

        $preferences = [
            ['name' => General::QUALITY, 'cast' => Cast::STRING],
            ['name' => General::LANGUAGE, 'cast' => Cast::INT],
        ];

        PreferenceBuilder::initBulk($preferences);

        $this->assertDatabaseCount((new Preference())->getTable(), 2);
        $this->assertDatabaseHas((new Preference())->getTable(), ['name' => General::LANGUAGE]);

        $found = Preference::query()->where('name', "=", General::LANGUAGE);
        $this->assertEquals(1, $found->count());
        $this->assertEquals(Cast::INT, $found->first()->cast);
    }


    /** @test */

    public function init_bulk_handles_mixed_valid_and_invalid_preferences()
    {
        $preferences = [
            ['name' => General::LANGUAGE, 'cast' => Cast::STRING],
            ['cast' => Cast::INT], // Missing 'name'
            ['name' => VideoPreferences::LANGUAGE, 'cast' => Cast::BOOL, 'default_value' => 10, 'rule' => new LowerThanRule(5)] // Fails rule
        ];

        $this->expectException(\InvalidArgumentException::class);
        PreferenceBuilder::initBulk($preferences);
    }
    /** @test */

    public function init_bulk_handles_deprecated_group()
    {
        $preferences = [
            ['name' => General::LANGUAGE, 'cast' => Cast::STRING,'group'=>"hi"],
  ];

        $this->expectException(\InvalidArgumentException::class);
        PreferenceBuilder::initBulk($preferences);
    }

    /** @test */

    public function init_bulk_with_all_options()
    {
        $preferences = [
            ['name' => VideoPreferences::LANGUAGE, 'cast' => Cast::BOOL, 'default_value' => 2, 'rule' => new LowerThanRule(5), 'description' => 'volume'],
            ['name' => General::LANGUAGE, 'cast' => Cast::BOOL, 'default_value' => 2, 'rule' => new LowerThanRule(5), 'description' => 'volume']
        ];

        PreferenceBuilder::initBulk($preferences);
        $this->assertDatabaseCount((new Preference())->getTable(), 2);
        $this->assertEquals(true,$this->testUser->getPreference(General::LANGUAGE));

        PreferenceBuilder::deleteBulk($preferences);
        $this->assertDatabaseCount((new Preference())->getTable(), 0);

    }

}

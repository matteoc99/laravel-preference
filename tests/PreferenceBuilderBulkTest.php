<?php

namespace Matteoc99\LaravelPreference\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Tests\Models\LowerThanRule;

class PreferenceBuilderBulkTest extends TestCase
{
    use RefreshDatabase;

    // Reset the database after each test

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
            ['name' => 'pref2', 'cast' => Cast::INT],
        ];

        $this->expectException(\InvalidArgumentException::class);
        PreferenceBuilder::initBulk($preferences);
    }


    /** @test */
    public function init_bulk_correctly_creates_multiple_preferences()
    {
        $preferences = [
            ['name' => 'pref1', 'cast' => Cast::STRING],
            ['name' => 'pref2', 'cast' => Cast::INT, 'group' => 'notifications'],
        ];

        PreferenceBuilder::initBulk($preferences);

        $this->assertDatabaseCount('preferences', 2);
        $this->assertDatabaseHas('preferences', ['name' => 'pref1']);
        $this->assertDatabaseHas('preferences', ['name' => 'pref2', 'group' => 'notifications']);
    }

    /** @test */
    public function delete_bulk_deletes_correct_preferences()
    {
        PreferenceBuilder::initBulk([
            ['name' => 'to_delete1', 'cast' => Cast::STRING],
            ['name' => 'to_delete2', 'cast' => Cast::INT, 'group' => 'other'],
            ['name' => 'keep', 'cast' => Cast::BOOL],
        ]);

        $deletePreferences = [
            ['name' => 'to_delete1'],
            ['name' => 'to_delete2', 'group' => 'other']
        ];

        PreferenceBuilder::deleteBulk($deletePreferences);

        $this->assertDatabaseCount('preferences', 1);
        $this->assertDatabaseHas('preferences', ['name' => 'keep']);
    }

    /** @test */
    public function init_bulk_throws_exception_with_invalid_cast()
    {
        $preferences = [
            ['name' => 'pref1', 'cast' => 'invalid_cast']
        ];

        $this->expectException(\InvalidArgumentException::class);
        PreferenceBuilder::initBulk($preferences);
    }

    /** @test */
    public function init_bulk_throws_exception_if_rule_validation_fails()
    {
        $preferences = [
            ['name' => 'pref1', 'cast' => Cast::INT, 'default_value' => 10, 'rule' => new LowerThanRule(5)]
        ];

        $this->expectException(\InvalidArgumentException::class);
        PreferenceBuilder::initBulk($preferences);
    }

    /** @test */
    public function init_bulk_creates_new_and_updates_existing_preferences()
    {
        // Create an initial preference
        PreferenceBuilder::init('existing_pref')->create();

        $preferences = [
            ['name' => 'new_pref', 'cast' => Cast::STRING],
            ['name' => 'existing_pref', 'cast' => Cast::INT],
        ];

        PreferenceBuilder::initBulk($preferences);

        $this->assertDatabaseCount('preferences', 2);
        $this->assertDatabaseHas('preferences', ['name' => 'new_pref']);

        $found = Preference::query()->where('name',"=",'existing_pref');
        $this->assertEquals(1, $found->count());
        $this->assertEquals(Cast::INT, $found->first()->cast);
    }

    public function delete_bulk_does_not_deletes_all_matching_preferences_when_multiple_exist()
    {
        PreferenceBuilder::initBulk([
            ['name' => 'to_delete', 'cast' => Cast::STRING],
            ['name' => 'to_delete', 'cast' => Cast::STRING, 'group' => 'other'],
        ]);

        PreferenceBuilder::deleteBulk([['name' => 'to_delete']]);

        $this->assertDatabaseCount('preferences', 1);
    }

    /** @test */

    public function init_bulk_handles_mixed_valid_and_invalid_preferences()
    {
        $preferences = [
            ['name' => 'pref1', 'cast' => Cast::STRING],
            ['cast' => Cast::INT], // Missing 'name'
            ['name' => 'pref2', 'cast' => Cast::BOOL, 'default_value' => 10, 'rule' => new LowerThanRule(5)] // Fails rule
        ];

        // Might need to adjust expected behavior based on your implementation
        $this->expectException(\InvalidArgumentException::class);
        PreferenceBuilder::initBulk($preferences);
    }

}

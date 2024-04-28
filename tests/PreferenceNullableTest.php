<?php

namespace Matteoc99\LaravelPreference\Tests;

use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\General;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Models\LowerThanRule;

class PreferenceNullableTest  extends TestCase
{
    /** @test */
    public function preference_can_be_nullable()
    {
        PreferenceBuilder::init(General::OPTIONS, Cast::BACKED_ENUM)
            ->nullable()
            ->create();

        $this->testUser->setPreference(General::OPTIONS, null);
        $this->assertNull($this->testUser->getPreference(General::OPTIONS));
    }

    /** @test */
    public function preference_nullable_set_through_array()
    {
        $preferences = [
            ['name' => General::LANGUAGE, 'cast' => Cast::INT, 'default_value' => 2, 'rule' => new LowerThanRule(5), "nullable" => true]
        ];

        PreferenceBuilder::initBulk($preferences);

        $this->testUser->setPreference(General::LANGUAGE, null);
        $this->assertNull($this->testUser->getPreference(General::LANGUAGE));
    }

    /** @test */
    public function single_mode_handles_nullable_correctly()
    {
        PreferenceBuilder::init(General::LANGUAGE)
            ->nullable()
            ->create();

        $this->testUser->setPreference(General::LANGUAGE, null);
        $this->assertNull($this->testUser->getPreference(General::LANGUAGE));
    }

    /** @test */
    public function preference_nullable_set_through_bulk_default()
    {
        $preferences = [
            ['name' => General::LANGUAGE, 'cast' => Cast::STRING, 'default_value' => 'English']
        ];

        PreferenceBuilder::initBulk($preferences, true);

        $this->testUser->setPreference(General::LANGUAGE, null);
        $this->assertNull($this->testUser->getPreference(General::LANGUAGE));
    }

    /** @test */
    public function preference_non_nullable_rejects_null()
    {
        PreferenceBuilder::init(General::CONFIG)->create();

        $this->expectException(ValidationException::class);
        $this->testUser->setPreference(General::CONFIG, null);
    }

    /** @test */
    public function bulk_creation_with_mixed_nullable_settings()
    {
        $preferences = [
            ['name' => General::CONFIG, 'cast' => Cast::STRING, 'nullable' => false],
            ['name' => General::LANGUAGE, 'cast' => Cast::STRING, 'nullable' => true]
        ];

        PreferenceBuilder::initBulk($preferences);

        $this->expectException(ValidationException::class);
        $this->testUser->setPreference(General::CONFIG, null);

        $this->testUser->setPreference(General::LANGUAGE, null);
        $this->assertNull($this->testUser->getPreference(General::LANGUAGE));
    }

    /** @test */
    public function bulk_nullable_default_applies_correctly()
    {
        $preferences = [
            ['name' => General::OPTIONS, 'cast' => Cast::BACKED_ENUM, 'default_value' => General::LANGUAGE]
        ];

        PreferenceBuilder::initBulk($preferences, true);

        $this->testUser->setPreference(General::OPTIONS, null);
        $this->assertNull($this->testUser->getPreference(General::OPTIONS));
    }


    /** @test */
    public function it_handles_nullable_with_default_value()
    {
        PreferenceBuilder::init(General::LANGUAGE)
            ->nullable()
            ->withDefaultValue('English')
            ->create();

        $this->testUser->setPreference(General::LANGUAGE, null);
        $this->assertNull($this->testUser->getPreference(General::LANGUAGE));
    }

    /** @test */
    public function preferences_bulk_creation_with_invalid_types_throws_exception()
    {
        $this->expectException(InvalidArgumentException::class);
        PreferenceBuilder::initBulk([
            ['name' => 'InvalidType', 'cast' => Cast::STRING]
        ]);
    }

}
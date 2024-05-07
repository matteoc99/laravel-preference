<?php

namespace Matteoc99\LaravelPreference\Tests;

use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\General;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\PlainEnum;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\Theme;

class AllowedClassesTest extends TestCase
{
    /** @test */
    public function init_throws_exception_for_invalid_enum_class()
    {
        $this->expectException(InvalidArgumentException::class);
        PreferenceBuilder::init(General::CONFIG, Cast::ENUM)
            ->setAllowedClasses('InvalidEnumClass')
            ->create();
    }

    /** @test */
    public function init_fails_for_unallowed_enum_value()
    {
        PreferenceBuilder::init(General::THEME, Cast::ENUM)
            ->setAllowedClasses(Theme::class)
            ->withDefaultValue(Theme::LIGHT)
            ->create();

        $this->expectException(ValidationException::class);
        $this->testUser->setPreference(General::THEME, 'UNDEFINED');
    }

    /** @test */
    public function single_mode_enforces_allowed_enum_values()
    {

        PreferenceBuilder::init(General::CONFIG, Cast::ENUM)
            ->setAllowedClasses(Theme::class)
            ->create();

        $this->expectException(ValidationException::class);
        $this->testUser->setPreference(General::CONFIG, PlainEnum::CONFIG);
    }

    /** @test */
    public function single_mode_enforces_allowed_default_values()
    {
        $this->expectException(ValidationException::class);
        PreferenceBuilder::init(General::CONFIG, Cast::ENUM)
            ->setAllowedClasses(Theme::class)
            ->withDefaultValue(PlainEnum::QUALITY)
            ->create();
    }

    /** @test */
    public function init_success_for_allowed_enum_value()
    {
        PreferenceBuilder::init(General::THEME, Cast::ENUM)
            ->setAllowedClasses(Theme::class)
            ->withDefaultValue(Theme::DARK)
            ->create();

        $this->testUser->setPreference(General::THEME, Theme::DARK);
        $this->assertEquals(Theme::DARK, $this->testUser->getPreference(General::THEME));
    }

    /** @test */
    public function init_accepts_multiple_allowed_enums()
    {
        PreferenceBuilder::init(General::CONFIG, Cast::ENUM)
            ->setAllowedClasses(Theme::class, PlainEnum::class)
            ->create();

        $this->testUser->setPreference(General::CONFIG, Theme::DARK);
        $this->testUser->setPreference(General::CONFIG, PlainEnum::LANGUAGE);
        $this->assertEquals(PlainEnum::LANGUAGE, $this->testUser->getPreference(General::CONFIG));

    }

    /** @test */
    public function init_bulk_throws_exception_for_invalid_enum_class()
    {
        $preferences = [
            ['name' => General::CONFIG, 'cast' => Cast::ENUM, 'allowed_values' => ['InvalidEnumClass']],
        ];

        $this->expectException(InvalidArgumentException::class);
        PreferenceBuilder::initBulk($preferences);
    }

    /** @test */
    public function init_bulk_fails_for_unallowed_enum_value()
    {
        $preferences = [
            ['name' => General::THEME, 'cast' => Cast::ENUM, 'allowed_values' => [Theme::class]],
        ];

        PreferenceBuilder::initBulk($preferences);

        $this->expectException(ValidationException::class);
        $this->testUser->setPreference(General::THEME, 'UNDEFINED');
    }

    /** @test */
    public function init_bulk_fails_for_unallowed_default_value()
    {
        $preferences = [
            ['name' => General::THEME, 'cast' => Cast::ENUM, 'allowed_values' => [Theme::class], 'default_value' => PlainEnum::QUALITY],
        ];

        $this->expectException(ValidationException::class);
        PreferenceBuilder::initBulk($preferences);
    }

    /** @test */
    public function init_bulk_success_for_allowed_enum_value()
    {
        $preferences = [
            ['name' => General::THEME, 'cast' => Cast::ENUM, 'allowed_values' => [Theme::class], 'default_value' => Theme::LIGHT],
        ];

        PreferenceBuilder::initBulk($preferences);
        $this->assertEquals(Theme::LIGHT, $this->testUser->getPreference(General::THEME));
    }

    /** @test */
    public function init_bulk_with_mixed_allowed_enums()
    {
        $preferences = [
            ['name' => General::THEME, 'cast' => Cast::ENUM, 'allowed_values' => [Theme::class], 'default_value' => Theme::LIGHT],
            ['name' => General::CONFIG, 'cast' => Cast::ENUM, 'allowed_values' => [PlainEnum::class], 'default_value' => PlainEnum::CONFIG],
        ];

        PreferenceBuilder::initBulk($preferences);
        $this->assertEquals(Theme::LIGHT, $this->testUser->getPreference(General::THEME));
        $this->assertEquals(PlainEnum::CONFIG, $this->testUser->getPreference(General::CONFIG));
    }
}

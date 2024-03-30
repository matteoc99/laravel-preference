<?php

namespace Matteoc99\LaravelPreference\Tests;

use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\General;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\OtherPreferences;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Models\CustomCast;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Models\LowerThanRule;

class PreferenceCastTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();

        PreferenceBuilder::init(General::VOLUME, Cast::INT)
            ->withRule(new LowerThanRule(5))
            ->withDefaultValue(1)
            ->create();

        PreferenceBuilder::init(General::EMAILS, Cast::BOOL)
            ->withDefaultValue(true)
            ->create();

        PreferenceBuilder::init(General::BIRTHDAY, Cast::DATE)->create();

        PreferenceBuilder::init(General::TIMEZONE, CustomCast::TIMEZONE)->create();
        PreferenceBuilder::init(General::OPTIONS, Cast::BACKED_ENUM)->create();

    }

    public function tearDown(): void
    {
        PreferenceBuilder::delete(General::VOLUME);
        PreferenceBuilder::delete(General::EMAILS);
        PreferenceBuilder::delete(General::BIRTHDAY);
        PreferenceBuilder::delete(General::TIMEZONE);
        PreferenceBuilder::delete(General::OPTIONS);

        parent::tearDown();
    }

    /** @test */
    public function user_can_set_and_get_integer_preference_with_custom_rule()
    {
        $this->testUser->setPreference(General::VOLUME, 3);

        $preference = $this->testUser->getPreference(General::VOLUME);
        $this->assertEquals(3, $preference);

        $this->expectException(ValidationException::class);
        $this->testUser->setPreference(General::VOLUME, 6);
    }

    /** @test */
    public function user_can_set_and_get_boolean_preference()
    {
        $this->testUser->setPreference(General::EMAILS, false);
        $preference = $this->testUser->getPreference(General::EMAILS);
        $this->assertFalse($preference);

        $this->testUser->removePreference(General::EMAILS);
        $preference = $this->testUser->getPreference(General::EMAILS);
        $this->assertTrue($preference);
    }

    /** @test */
    public function user_can_set_and_get_date_preference()
    {
        $birthday = Carbon::now()->subYears(25);

        $this->testUser->setPreference(General::BIRTHDAY, $birthday);

        $preference = $this->testUser->getPreference(General::BIRTHDAY);

        $this->assertEquals($birthday->toDateString(), $preference->toDateString());
    }

    /** @test */
    public function user_can_set_and_get_preference_with_custom_cast()
    {
        $this->testUser->setPreference(General::TIMEZONE, 'Europe/Berlin');

        $preference = $this->testUser->getPreference(General::TIMEZONE);

        $this->assertEquals('Europe/Berlin', $preference);

        $this->expectException(ValidationException::class);
        $this->testUser->setPreference(General::TIMEZONE, "France");
    }

    /** @test */
    public function user_can_set_and_get_preference_with_enum_cast()
    {


        $this->testUser->setPreference(General::OPTIONS, OtherPreferences::CONFIG);
        $preference = $this->testUser->getPreference(General::OPTIONS);
        $this->assertEquals(OtherPreferences::CONFIG, $preference);


        $this->expectException(ValidationException::class);
        $this->testUser->setPreference(General::OPTIONS, 'Europe/Berlin');

    }
}
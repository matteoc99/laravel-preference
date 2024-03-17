<?php

namespace Matteoc99\LaravelPreference\Tests;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Validation\ValidationException;
use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Tests\Models\CustomCast;
use Matteoc99\LaravelPreference\Tests\Models\LowerThanRule;

class PreferenceCastTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        PreferenceBuilder::init("volume", Cast::INT)
            ->withRule(new LowerThanRule(5))
            ->withDefaultValue(1)
            ->create();

        PreferenceBuilder::init("receive_emails", Cast::BOOL)
            ->withDefaultValue(true)
            ->create();

        PreferenceBuilder::init("birthday", Cast::DATE)->create();

        PreferenceBuilder::init("timezone", CustomCast::TIMEZONE)->create();

    }

    public function tearDown(): void
    {
        PreferenceBuilder::init("volume")->delete();
        PreferenceBuilder::init("receive_emails")->delete();
        PreferenceBuilder::init("birthday")->delete();
        PreferenceBuilder::init("timezone")->delete();

        parent::tearDown();
    }

    /** @test */
    public function user_can_set_and_get_integer_preference_with_custom_rule()
    {
        $this->testUser->setPreference('volume', 3);

        $preference = $this->testUser->getPreference('volume');
        $this->assertEquals(3, $preference);

        $this->expectException(ValidationException::class);
        $this->testUser->setPreference('volume', 6);
    }
    /** @test */
    public function user_can_set_and_get_boolean_preference()
    {
        $this->testUser->setPreference('receive_emails', false);
        $preference = $this->testUser->getPreference('receive_emails');
        $this->assertFalse($preference);

        $this->testUser->removePreference('receive_emails');
        $preference = $this->testUser->getPreference('receive_emails');
        $this->assertTrue($preference);
    }

    /** @test */
    public function user_can_set_and_get_date_preference()
    {
        $birthday = Carbon::now()->subYears(25);

        $this->testUser->setPreference('birthday', $birthday);

        $preference = $this->testUser->getPreference('birthday');

        $this->assertEquals($birthday->toDateString(), $preference->toDateString());
    }

    /** @test */
    public function user_can_set_and_get_preference_with_custom_cast()
    {
        $this->testUser->setPreference('timezone', 'Europe/Berlin');

        $preference = $this->testUser->getPreference('timezone');

        $this->assertEquals('Europe/Berlin', $preference);

        $this->expectException(ValidationException::class);
        $this->testUser->setPreference('timezone', "France");
    }
}
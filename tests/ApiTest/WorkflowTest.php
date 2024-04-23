<?php

namespace Matteoc99\LaravelPreference\Tests\ApiTest;

use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Rules\BetweenRule;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\General;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\OtherPreferences;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\VideoPreferences;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Models\LowerThanRule;
use Matteoc99\LaravelPreference\Utils\ConfigHelper;

class WorkflowTest extends ApiTestCase
{


    /** @test */
    public function test_workflow()
    {

        $general = $this->get(route('preferences.user.general.get', ['scope_id' => 1, 'preference' => 'language']));
        $video   = $this->get(route('preferences.user.video.get', ['scope_id' => 1, 'preference' => 'language']));

        $general->assertSuccessful();
        $video->assertNotFound();

    }

    /** @test */
    public function test_int_workflow()
    {
        PreferenceBuilder::init(VideoPreferences::QUALITY, Cast::INT)
            ->withDefaultValue(2)
            ->withRule(new LowerThanRule(5))
            ->create();

        $video = $this->get(route('preferences.user.video.get', ['scope_id' => 1, 'preference' => 'quality']));
        $video->assertSuccessful();

        $video->assertJson(['value' => 2]);

        $video = $this->patch(route('preferences.user.video.update', ['scope_id' => 1, 'preference' => 'quality']), [
            'value' => 4
        ]);
        $video->assertJson(['value' => 4]);

        $video = $this->delete(route('preferences.user.video.delete', ['scope_id' => 1, 'preference' => 'quality']));

        $video->assertJson(['value' => 2]);

        $video = $this->patch(route('preferences.user.video.update', ['scope_id' => 1, 'preference' => 'quality']), [
            'value' => 40
        ]);

        $video->assertRedirect();
    }

    /** @test */

    public function test_xss_workflow()
    {
        PreferenceBuilder::init(General::EMAILS)->create();

        $xssInput = '<span/onmouseover=confirm(1)>X</span>';

        $response = $this->patch(route('preferences.user.general.update', ['scope_id' => 1, 'preference' => 'emails']), [
            'value' => $xssInput
        ]);

        $response->assertSuccessful();

        $email = $this->get(route('preferences.user.general.get', ['scope_id' => 1, 'preference' => 'emails']));

        if (ConfigHelper::isXssCleanEnabled()) {
            $email->assertJson(['value' => '<span/>X</span>']);
        } else {
            $email->assertJson(['value' => $xssInput]);
        }
    }

    /** @test */
    public function test_none_workflow()
    {
        // 'none' might represent no specific data transformation or casting
        PreferenceBuilder::init(General::QUALITY, Cast::NONE)
            ->withDefaultValue('light')
            ->create();

        $theme = $this->get(route('preferences.user.general.get', ['scope_id' => 1, 'preference' => 'quality']));
        $theme->assertSuccessful();
        $theme->assertJson(['value' => 'light']);
    }


    /** @test */
    public function test_float_workflow()
    {
        PreferenceBuilder::init(VideoPreferences::QUALITY, Cast::FLOAT)
            ->withDefaultValue(1.5)
            ->withRule(new BetweenRule(0.0, 2.0))
            ->create();

        $brightness = $this->patch(route('preferences.user.video.update', ['scope_id' => 1, 'preference' => 'quality']), ['value' => 1.75]);
        $brightness->assertJson(['value' => 1.75]);

        $brightness = $this->patch(route('preferences.user.video.update', ['scope_id' => 1, 'preference' => 'quality']), ['value' => 2.5]);
        $brightness->assertRedirect(); // Should fail due to rule
    }

    /** @test */
    public function test_bool_workflow()
    {
        PreferenceBuilder::init(General::EMAILS, Cast::BOOL)
            ->withDefaultValue(true)
            ->create();

        $notification = $this->patch(route('preferences.user.general.update', ['scope_id' => 1, 'preference' => 'emails']), ['value' => false]);
        $notification->assertJson(['value' => false]);
    }


    /** @test */
    public function test_array_workflow()
    {
        PreferenceBuilder::init(General::CONFIG, Cast::ARRAY)
            ->withDefaultValue(['action', 'adventure'])
            ->create();

        $genres = $this->patch(route('preferences.user.general.update', ['scope_id' => 1, 'preference' => 'config']), ['value' => ['comedy', 'drama']]);
        $genres->assertJson(['value' => ['comedy', 'drama']]);
    }


    /** @test */
    public function test_date_workflow()
    {
        PreferenceBuilder::init(General::BIRTHDAY, Cast::DATE)
            ->withDefaultValue('1990-01-01')
            ->create();

        $birthday = $this->patch(route('preferences.user.general.update', ['scope_id' => 1, 'preference' => 'birthday']), ['value' => '2000-02-29']);
        $birthday->assertJson(['value' => '2000-02-29']);
    }

    /** @test */
    public function test_time_workflow()
    {
        PreferenceBuilder::init(General::REMINDER, Cast::TIME)
            ->withDefaultValue('08:00:00')
            ->create();

        $reminder = $this->patch(route('preferences.user.general.update', ['scope_id' => 1, 'preference' => 'reminder']), ['value' => '09:30:00']);
        $reminder->assertJson(['value' => '09:30:00']);
    }

    /** @test */
    public function test_datetime_workflow()
    {
        PreferenceBuilder::init(General::BIRTHDAY, Cast::DATETIME)
            ->withDefaultValue('2023-01-01 12:00:00')
            ->create();

        $event = $this->patch(route('preferences.user.general.update', ['scope_id' => 1, 'preference' => 'birthday']), ['value' => '2023-12-25 15:00:00']);
        $event->assertJson(['value' => '2023-12-25 15:00:00']);
    }

    /** @test */
    public function test_timestamp_workflow()
    {
        PreferenceBuilder::init(General::REMINDER, Cast::TIMESTAMP)
            ->withDefaultValue(time())
            ->create();
        sleep(1);
        $time      = time();
        $lastLogin = $this->patch(route('preferences.user.general.update', ['scope_id' => 1, 'preference' => 'reminder']), ['value' => $time]);
        $lastLogin->assertJson(['value' => $time]);
    }

    /** @test */
    public function test_backed_enum_workflow()
    {
        PreferenceBuilder::init(General::CONFIG, Cast::BACKED_ENUM)
            ->withDefaultValue(OtherPreferences::QUALITY)
            ->create();

        $config = $this->patch(route('preferences.user.general.update', ['scope_id' => 1, 'preference' => 'config']), ['value' => OtherPreferences::CONFIG->value]);
        $config->assertRedirect();
    }


    /** @test */
    public function test_object_workflow()
    {
        PreferenceBuilder::init(General::CONFIG, Cast::OBJECT)
            ->withDefaultValue($this->adminUser)
            ->create();

        $profile = $this->patch(route('preferences.user.general.update', ['scope_id' => 1, 'preference' => 'config']), ['value' => ['name' => 'Jane Doe', 'email' => 'janedoe@example.com']]);
        $profile->assertRedirect();
    }
}
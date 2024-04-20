<?php

namespace Matteoc99\LaravelPreference\Tests\ApiTest;

use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\General;
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


}
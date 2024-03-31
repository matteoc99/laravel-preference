<?php

namespace Matteoc99\LaravelPreference\Tests\ApiTest;

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
    public function test_get_and_set()
    {

        $video = $this->get(route('preferences.user.video.get', ['scope_id' => 1, 'preference' => 'quality']));
        $video->assertSuccessful();

        $video->assertJson(['value'=>2]);

        $video = $this->patch(route('preferences.user.video.update', ['scope_id' => 1, 'preference' => 'quality']),[
            'value'=>4
        ]);
        $video->assertJson(['value'=>4]);


        $video = $this->patch(route('preferences.user.video.update', ['scope_id' => 1, 'preference' => 'quality']),[
            'value'=>40
        ]);

        $video->assertRedirect();
    }

}
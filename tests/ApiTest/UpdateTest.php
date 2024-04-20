<?php

namespace Matteoc99\LaravelPreference\Tests\ApiTest;

class UpdateTest extends ApiTestCase
{
    /** @test */
    public function test_update_action()
    {

        $response = $this->patch(route('preferences.user.general.update', ['scope_id' => 1,'preference' => 'language']));

        $response->assertStatus(302);
    }

    /** @test */

    public function test_update_validation_fail_action()
    {

        $response = $this->patch(route('preferences.user.general.update', ['scope_id' => 1,'preference' => 'language']),[
           'value'=> '1'
        ]);

        $response->assertRedirect();
    }

    /** @test */

    public function test_update_success_action()
    {

        $response = $this->patch(route('preferences.user.general.update', ['scope_id' => 1,'preference' => 'language']),[
            'value'=> 'de'
        ]);

        $response->assertSuccessful();
    }

    /** @test */

    public function test_update_invalid_scope()
    {
        $response = $this->patch(route('preferences.user.general.update', ['scope_id' => 200, 'preference' => 'language']));

        $response->assertNotFound();
    }

    /** @test */
    public function test_update_invalid_pref()
    {
        $response = $this->patch(route('preferences.user.general.update', ['scope_id' => 1, 'preference' => 'languageeeee']));

        $response->assertNotFound();
    }
}
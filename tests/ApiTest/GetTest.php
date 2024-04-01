<?php

namespace Matteoc99\LaravelPreference\Tests\ApiTest;

class GetTest extends ApiTestCase
{
    /** @test */
    public function test_get_action()
    {

        $response = $this->get(route('preferences.user.general.get', ['scope_id' => 1, 'preference' => 'language']));

        $response->assertStatus(200);
    }

    /** @test */

    public function test_get_invalid_scope()
    {
        $response = $this->get(route('preferences.user.general.get', ['scope_id' => 200, 'preference' => 'language']));

        $response->assertNotFound();
    }
    /** @test */

    public function test_get_invalid_permission()
    {
        $response = $this->get(route('preferences.user.general.get', ['scope_id' => 2, 'preference' => 'language']));

        $response->assertForbidden();
    }

    /** @test */
    public function test_get_invalid_pref()
    {
        $response = $this->get(route('preferences.user.general.get', ['scope_id' => 1, 'preference' => 'languageeee']));

        $response->assertNotFound();
    }
}
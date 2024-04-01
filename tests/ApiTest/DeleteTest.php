<?php

namespace Matteoc99\LaravelPreference\Tests\ApiTest;

class DeleteTest extends ApiTestCase
{
    /** @test */
    public function test_delete_action()
    {

        $response = $this->delete(route('preferences.user.general.delete', ['scope_id' => 1,'preference' => 'language']));

        $response->assertStatus(200);
    }

    public function test_delete_invalid_scope()
    {
        $response = $this->delete(route('preferences.user.general.delete', ['scope_id' => 200, 'preference' => 'language']));

        $response->assertNotFound();
    }
    public function test_delete_invalid_permission()
    {
        $response = $this->delete(route('preferences.user.general.delete', ['scope_id' => 2, 'preference' => 'language']));

        $response->assertForbidden();
    }

    /** @test */

    public function test_delete_invalid_pref()
    {
        $response = $this->delete(route('preferences.user.general.delete', ['scope_id' => 1, 'preference' => 'languageee']));

        $response->assertNotFound();
    }
}
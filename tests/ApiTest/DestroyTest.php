<?php

namespace Matteoc99\LaravelPreference\Tests\ApiTest;

class DestroyTest extends ApiTestCase
{
    /** @test */
    public function test_destroy_action()
    {

        $response = $this->delete(route('preferences.user.general.destroy', ['scope_id' => 1,'preference' => 'language']));

        $response->assertStatus(200);
    }

    public function test_destroy_invalid_scope()
    {
        $response = $this->delete(route('preferences.user.general.destroy', ['scope_id' => 2, 'preference' => 'language']));

        $response->assertNotFound();
    }

    /** @test */

    public function test_destroy_invalid_pref()
    {
        $response = $this->delete(route('preferences.user.general.destroy', ['scope_id' => 1, 'preference' => 'languageee']));

        $response->assertNotFound();
    }
}
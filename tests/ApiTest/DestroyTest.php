<?php

namespace Matteoc99\LaravelPreference\Tests\ApiTest;

class DestroyTest extends ApiTestCase
{
    /** @test */
    public function test_destroy_action()
    {

        $response = $this->get(route('preferences.user.general.destroy', ['scope_id' => 1,'preference' => 'language']));

        $response->assertStatus(200);
    }
}
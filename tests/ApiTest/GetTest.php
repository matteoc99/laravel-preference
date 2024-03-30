<?php

namespace Matteoc99\LaravelPreference\Tests\ApiTest;

class GetTest extends ApiTestCase
{
    /** @test */
    public function test_get_action()
    {

        $response = $this->get(route('preferences.user.general.get', ['scope_id' => 1,'preference' => 'language']));

        $response->assertStatus(200);
    }
}
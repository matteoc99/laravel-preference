<?php

namespace Matteoc99\LaravelPreference\Tests\ApiTest;

class UpdateTest extends ApiTestCase
{
    /** @test */
    public function test_update_action()
    {

        $response = $this->get(route('preferences.user.general.update', ['scope_id' => 1,'preference' => 'language']));

        $response->assertStatus(200);
    }
}
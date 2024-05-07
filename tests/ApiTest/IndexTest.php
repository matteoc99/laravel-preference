<?php

namespace Matteoc99\LaravelPreference\Tests\ApiTest;

class IndexTest extends ApiTestCase
{
    /** @test */
    public function test_index_action()
    {

        $response = $this->get(route('preferences.user.general.index', ['scope_id' => 1]));

        $response->assertStatus(200);
    }

    /** @test */
    public function test_index_invalid_scope()
    {
        $response = $this->get(route('preferences.user.general.index', ['scope_id' => 200]));

        $response->assertNotFound();
    }
}

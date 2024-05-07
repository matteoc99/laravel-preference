<?php

namespace Matteoc99\LaravelPreference\Tests;

class MigrationTest extends TestCase
{
    /** @test */
    public function migration_rollback_works()
    {
        for ($i = 0; $i < 10; $i++) {

            $this->artisan('migrate:rollback', ['--database' => 'testbench'])->run();
            $this->artisan('migrate', ['--database' => 'testbench'])->run();
        }

        $this->assertTrue($this->getSchema()->hasTable('custom_prefs'));
        $this->assertCount(4, $this->getSchema()->getTables());
    }
}

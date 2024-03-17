<?php

namespace Matteoc99\LaravelPreference\Tests;

use Illuminate\Database\Schema\Blueprint;
use Matteoc99\LaravelPreference\PreferenceServiceProvider;
use Matteoc99\LaravelPreference\Tests\Models\User;

class TestCase extends \Orchestra\Testbench\TestCase
{

    protected User $testUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $this->setUpDatabase($this->app);


        $this->testUser = new User([
            'email' => 'test@test.com'
        ]);
        $this->testUser->save();
        $this->testUser = $this->testUser->fresh();
    }

    /**
     * add the package provider
     *
     * @param $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [PreferenceServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function setUpDatabase($app)
    {
        $schema = $app['db']->connection()->getSchemaBuilder();

        $schema->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->softDeletes();
            $table->timestamps();
        });
    }
}
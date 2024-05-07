<?php

namespace Matteoc99\LaravelPreference\Tests;

use Exception;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Matteoc99\LaravelPreference\PreferenceServiceProvider;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Models\User;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected User $testUser;

    protected User $otherUser;

    protected User $adminUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $this->setUpDatabase();

        $this->otherUser = new User([
            'email' => 'other@test.com',
        ]);

        $this->testUser = new User([
            'email' => 'test@test.com',
        ]);

        $this->adminUser = new User([
            'email' => 'test@test.com',
            'admin' => true,
        ]);
        $this->testUser->save();
        $this->otherUser->save();
        $this->adminUser->save();

        Auth::login($this->testUser);
    }

    /**
     * add the package provider
     *
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
     * @param  Application  $app
     * @return void
     *
     * @throws Exception
     */
    protected function getEnvironmentSetUp($app)
    {
        $configPath = __DIR__.'/../config/user_preference.php';
        if (file_exists($configPath)) {
            $userPreferencesConfig = require $configPath;
            $app['config']->set('user_preference', $userPreferencesConfig);
        } else {
            throw new Exception("Configuration file not found at: {$configPath}");
        }
        $app['config']->set('database.table_prefix', 'testbench');
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);
        $app['config']->set('user_preference.db.preferences_table_name', 'custom_prefs');
        $app['config']->set('user_preference.db.user_preferences_table_name', 'custom_user_prefs');

    }

    protected function setUpDatabase()
    {

        $this->getSchema()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->boolean('admin')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /** @return Builder */
    protected function getSchema()
    {
        return $this->app['db']->connection()->getSchemaBuilder();
    }
}

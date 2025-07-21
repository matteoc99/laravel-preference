# Laravel Preference Package Development Guidelines

This document provides essential information for developers working on the Laravel Preference package.

## Build/Configuration Instructions

### Requirements

- PHP 8.2 or higher
- Laravel 10, 11, or 12
- Composer

### Installation

1. Install the package via Composer:

```bash
composer require matteoc99/laravel-preference
```

2. Publish the configuration file:

```bash
php artisan vendor:publish --tag="laravel-preference-config"
```

3. Run the migrations:

```bash
php artisan migrate
```

### Configuration Options

The package configuration file (`config/user_preference.php`) contains the following options:

#### Database Configuration

```php
'db' => [
    'connection' => null, // Database connection to use (null = default)
    'preferences_table_name' => 'preferences', // Table name for preferences
    'user_preferences_table_name' => 'users_preferences', // Table name for user preferences
],
```

#### XSS Cleaning

```php
'xss_cleaning' => true, // Enable/disable XSS cleaning for user input
```

For XSS cleaning to work, you should install the recommended package:

```bash
composer require graham-campbell/security-core:^4.0
```

#### API Routes Configuration

```php
'routes' => [
    'enabled' => false, // Enable/disable API routes
    'middlewares' => [
        'web', // Required for Auth::user() and policies
        'auth', // General middleware
        'user' => 'verified', // Optional, scoped middleware
        'user.general' => 'verified', // Optional, scoped & grouped middleware
    ],
    'prefix' => 'preferences', // URL prefix for routes
    'groups' => [
        // Enum class list of preferences
        // 'general' => General::class
    ],
    'scopes' => [
        // Preferenceable models
        'user' => \Illuminate\Auth\Authenticatable::class,
    ],
],
```

## Testing Information

### Running Tests

The package uses PHPUnit for testing. To run the tests:

```bash
composer test
```

To run a specific test:

```bash
composer test -- --filter=TestName
```

To generate a test coverage report:

```bash
composer coverage
```

This will generate HTML and XML coverage reports in the `reports/coverage` directory.

### Testing Locally with GitHub Actions

You can test the GitHub Actions workflows locally using [act](https://github.com/nektos/act):

```bash
composer pipeline-tests    # Run the tests workflow
composer pipeline-coverage # Run the coverage workflow
```

### Creating Tests

Tests should be placed in the `tests` directory. The package uses Orchestra Testbench for Laravel package testing.

#### Test Structure

1. Create a new test class that extends `Matteoc99\LaravelPreference\Tests\TestCase`
2. Use the `/** @test */` annotation to mark methods as tests
3. Use setUp() and tearDown() methods to initialize and clean up test data

#### Example Test

Here's a simple example test that demonstrates setting and getting preferences:

```php
<?php

namespace Matteoc99\LaravelPreference\Tests;

use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\General;

class ExampleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create a new preference for testing
        PreferenceBuilder::init(General::OPTIONS, Cast::ARRAY)
            ->withDefaultValue(['notifications' => true])
            ->create();
    }

    public function tearDown(): void
    {
        // Clean up the preference after testing
        PreferenceBuilder::delete(General::OPTIONS);

        parent::tearDown();
    }

    /** @test */
    public function it_can_set_and_get_preference()
    {
        // Set a preference for the test user
        $this->testUser->setPreference(General::OPTIONS, ['notifications' => false]);

        // Get the preference from the test user
        $preference = $this->testUser->getPreference(General::OPTIONS);

        // Assert that the preference value is correct
        $this->assertEquals(['notifications' => false], $preference);
    }
}
```

### Test Environment

The test environment is set up in the `TestCase` class:

- Uses an in-memory SQLite database
- Creates a users table with email and admin columns
- Creates three test users (testUser, otherUser, and adminUser)
- Logs in the testUser

## Additional Development Information

### Code Style

The package uses Laravel Pint for code style. To check the code style:

```bash
composer pint-test
```

To fix code style issues:

```bash
composer pint
```

### Static Analysis

The package uses PHPStan for static analysis. To run the analysis:

```bash
composer analyse
```

### Creating Preferences

Preferences are defined as string-backed enums that implement the `PreferenceGroup` interface:

```php
use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;

enum General: string implements PreferenceGroup
{
    case LANGUAGE = 'language';
    case THEME = 'theme';
    // ...
}
```

Preferences are created using the `PreferenceBuilder` class:

```php
use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;

// Create a string preference
PreferenceBuilder::init(General::LANGUAGE, Cast::STRING)
    ->withDefaultValue('en')
    ->create();

// Create an array preference
PreferenceBuilder::init(General::CONFIG, Cast::ARRAY)
    ->withDefaultValue(['key' => 'value'])
    ->create();
```

### Working with Preferences

To work with preferences, models should:

1. Implement the `PreferenceableModel` interface
2. Use the `HasPreferences` trait

```php
use Matteoc99\LaravelPreference\Contracts\PreferenceableModel;
use Matteoc99\LaravelPreference\Enums\PolicyAction;
use Matteoc99\LaravelPreference\Traits\HasPreferences;

class User extends \Illuminate\Foundation\Auth\User implements PreferenceableModel
{
    use HasPreferences;

    public function isUserAuthorized(?Authenticatable $user, PolicyAction $action): bool
    {
        return $user?->id == $this->id;
    }
}
```

Then you can set and get preferences:

```php
// Set a preference
$user->setPreference(General::LANGUAGE, 'en');

// Get a preference
$language = $user->getPreference(General::LANGUAGE);

// Remove a preference
$user->removePreference(General::LANGUAGE);

// Get all preferences of a specific type
$preferences = $user->getPreferences(General::class);

// Get all preferences
$allPreferences = $user->getPreferences();
```

### Custom Casts

You can create custom casts by implementing the `CastableEnum` interface:

```php
use Illuminate\Contracts\Validation\ValidationRule;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;

enum CustomCast: string implements CastableEnum
{
    case TIMEZONE = 'tz';

    public function validation(): ValidationRule|array|string|null
    {
        return match ($this) {
            self::TIMEZONE => 'timezone:all',
        };
    }

    public function castFromString(string $value): mixed
    {
        return match ($this) {
            self::TIMEZONE => $value,
        };
    }

    public function castToString(mixed $value): string
    {
        return match ($this) {
            self::TIMEZONE => (string)$value,
        };
    }
    
    public function castToDto(mixed $value): array
    {
        return ['value' => $value];
    } 
}
```

### Custom Validation Rules

You can create custom validation rules by implementing Laravel's `ValidationRule` interface:

```php
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CustomRule implements ValidationRule
{
    protected array $data;

    public function __construct(...$data)
    {
        $this->data = $data;
    }

    public function message()
    {
        return sprintf("Validation failed: %s", implode(", ", $this->data));
    }
    
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->isValid($value)) {
            $fail($this->message());
        }
    }
    
    private function isValid($value): bool
    {
        // Custom validation logic
        return true;
    }
}
```

### API Routes

To enable API routes, set `routes.enabled` to `true` in the configuration file. This will register routes for managing preferences via API.

The routes follow this pattern:

- GET `/{prefix}/{scope}/{scope_id}/{group}` - Get all preferences for a scope and group
- GET `/{prefix}/{scope}/{scope_id}/{group}/{preference}` - Get a specific preference
- PUT/PATCH `/{prefix}/{scope}/{scope_id}/{group}/{preference}` - Update a specific preference
- DELETE `/{prefix}/{scope}/{scope_id}/{group}/{preference}` - Delete a specific preference

For example, with the default configuration:

- GET `/preferences/user/1/general` - Get all general preferences for user 1
- GET `/preferences/user/1/general/language` - Get the language preference for user 1
- PATCH `/preferences/user/1/general/language` - Update the language preference for user 1
- DELETE `/preferences/user/1/general/language` - Delete the language preference for user 1
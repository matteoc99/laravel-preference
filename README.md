#  

# work in progress

# Laravel User Preferences

[![Latest Version on Packagist](https://img.shields.io/packagist/v/matteoc99/laravel-preference.svg?style=flat-square)](https://packagist.org/packages/matteoc99/laravel-preference)
[![Total Downloads](https://img.shields.io/packagist/dt/matteoc99/laravel-preference.svg?style=flat-square)](https://packagist.org/packages/matteoc99/laravel-preference)
[![Tests](https://github.com/matteoc99/laravel-preference/actions/workflows/tests.yml/badge.svg)](https://github.com/matteoc99/laravel-preference/actions/workflows/tests.yml)
[![codecov](https://codecov.io/github/matteoc99/laravel-preference/graph/badge.svg?token=GS19E2ORR4)](https://codecov.io/github/matteoc99/laravel-preference)

This Laravel package aims to store and manage user settings/preferences in a simple and scalable manner.

## Installation

You can install the package via composer:

```bash
composer require matteoc99/laravel-preference
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-preference-config"
```

```php
  'db' => [
        'connection' => null, //string: the connection name to use 
    ] // null -> use defaults
```

Run the migrations with:

```bash
php artisan migrate
```

## Usage

### Concepts

> Preferences are defined by their name, and optionally grouped

> Each preference has at least a name and a caster. For additional validation you can add you custom Rule object
>> The default caster supports all major primitives, including datetime/date and timestamp which get converted
> > with `Carbon/Carbon`

### Create a Preference
#### single mode
```php
 public function up(): void
    {
        PreferenceBuilder::init("language")
            ->withDefaultValue("en")
            // optional ->withGroup('general')
            ->withRule(new InRule("en", "it", "de"))
            ->create();
            
            // Or
            PreferenceBuilder::init("language")->create()

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        PreferenceBuilder::init("language")
        // optional if there  is only one language preference
        ->withGroup('general')
        ->delete();
    }
```
#### Bulk mode

```php
use Illuminate\Database\Migrations\Migration;
use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Rules\InRule;

return new class extends Migration {


    public function up(): void
    {

        PreferenceBuilder::initBulk($this->preferences());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        PreferenceBuilder::deleteBulk($this->preferences());
    }

    /**
     * Reverse the migrations.
     */
    public function preferences(): array
    {
       return [
           ['name' => 'language', 'cast' => Cast::STRING, 'default_value' => 'en', 'rule' => new InRule("en", "it", "de"), 'group' => 'general'],
           ['name' => 'theme', 'cast' => Cast::STRING, 'default_value' => 'light'],
            ['name' => 'configuration', 'cast' => Cast::ARRAY],
       ];
    }
};

```

### Working with preferences

> use the trait `HasPreferences`

```php
// remove a preference, reverting it to the default value if set.
public function removePreference(string $name, string $group = 'general'): void

// set / update a preference 
public function setPreference(string $name, mixed $value, string $group = 'general'): void

// collection of UserPreferences | optional filter by group    
public function getPreferences(string $group = null): Collection

// get the value of the preference | if no value or default_value are found, returns $default
public function getPreference(string $name, string $group = 'general', mixed $default = null): mixed
```

### Examples

```php
    $user->setPreference('language',"de");
    $user->getPreference('language'); // 'de' as string

    $user->setPreference('language',"fr"); 
    // ValidationException because of the rule: ->withRule(new InRule(["en","it","de"]))
    $user->setPreference('language',2); 
    // ValidationException because of the cast: Cast::STRING

    $user->removePreference('language'); 
    $user->getPreference('language'); // 'en' as string
```

## Casting

> set the cast when creating a Preference
>> PreferenceBuilder::init("language", Cast::STRING)

### Available Casts

INT, FLOAT, STRING, BOOL, ARRAY, TIME, DATE, DATETIME, TIMESTAMP

### Custom Caster

create a `BackedEnum`, and implement `CastableEnum`

```php
use Illuminate\Validation\Rule;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;

enum MyCast: string implements CastableEnum
{
    case TIMEZONE = 'tz';
 
    public function validation(): Rule|string
    {
        return match ($this) {
            self::TIMEZONE => 'timezone:all',
        };
    }

    public function castFromString(string $value): mixed
    {
        return match ($this) {
            self::TIMEZONE => (string)$value,
        
        };
    }
    public function castToString(mixed $value): string
    {
        return match ($this) {
            self::TIMEZONE => (string)$value,
        };
    } 
}

 PreferenceBuilder::init("timezone",MyCast::TIMEZONE)
 //->...etc

```

## Custom Rules

> rules need to implement `HasValidation`

> additionally, if your rule requires parameter, extend `DataRule`
>  which than will provide the parameters via `getData()`


```php
class MyRule extends DataRule
{
    public function passes($attribute, $value)
    {
        return Str::startsWith($value, $this->getData());
    }

    public function message()
    {
        return sprintf("Wrong Timezone, one of: %s expected", implode(", ",$this->getData()));
    }
}

 PreferenceBuilder::init("timezone",MyCast::TIMEZONE)
            ->withRule(new MyRule("Europe","Asia"))
```

## Test

`composer test ./tests`

## Security Vulnerabilities

Please review [our security policy](SECURITY.md) on how to report security vulnerabilities.

## Credits

- [matteoc99](https://github.com/mattoc99)

## License

The MIT License (MIT). Please check the [License File](LICENSE) for more information.

# Support target

| Package Version | Laravel Version |
|-----------------|-----------------|
| 1.x             | 10              |
| 2.x             | 11              |
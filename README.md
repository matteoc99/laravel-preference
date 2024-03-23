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

> Preferences are defined by their name
> 
> Each preference has at least a name and a caster. For additional validation you can add you custom Rule object
>> The default caster supports all major primitives, Enums, as well as time/datetime/date and timestamp which get converted
>> with `Carbon/Carbon`

### Create a Preference
#### single mode
```php
    public function up(): void
    {
        PreferenceBuilder::init(Preferences::LANGUAGE)
            ->withDefaultValue("en")
            ->withRule(new InRule("en", "it", "de"))
            ->create();
            
       
        // Or
        PreferenceBuilder::init(Preferences::LANGUAGE)->create()
        // different enums with the same value do not conflict
        PreferenceBuilder::init(OtherPreferences::LANGUAGE)->create()
        
        // update
        PreferenceBuilder::init(Preferences::LANGUAGE)
            ->withRule(new InRule("en", "it", "de"))
            ->updateOrCreate()
            
        // Discouraged, consider using Enums
        PreferenceBuilder::init("language")->create()
    }

    public function down(): void
    {
        PreferenceBuilder::delete(Preferences::LANGUAGE);
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
            ['name' => Preferences::LANGUAGE, 'cast' => Cast::STRING, 'default_value' => 'en', 'rule' => new InRule("en", "it", "de")],
            ['name' => Preferences::THEME, 'cast' => Cast::STRING, 'default_value' => 'light'],
            ['name' => Preferences::CONFIGURATION, 'cast' => Cast::ARRAY],
            ['name' => Preferences::CONFIGURATION, 'cast' => Cast::ARRAY],
       ];
    }
};

```

### Working with preferences

> use the trait `HasPreferences`

> string will be deprecated, consider sticking to `UnitEnum`

```php
// remove a preference, reverting it to the default value if set.
public function removePreference(UnitEnum|string $name, string $group = null): void

// set / update a preference 
public function setPreference(UnitEnum|string $name, mixed $value, string $group = null): void

// collection of UserPreferences | optional filter by group    
public function getPreferences(string $group = null): Collection

// get the value of the preference | if no value or default_value are found, returns $default
public function getPreference(UnitEnum|string $name, string $group = null, mixed $default = null): mixed
```

### Examples

```php
    $user->setPreference(UserPreferences::LANGUAGE,"de");
    $user->getPreference(UserPreferences::LANGUAGE,); // 'de' as string

    $user->setPreference(UserPreferences::LANGUAGE,,"fr"); 
    // ValidationException because of the rule: ->withRule(new InRule("en","it","de"))
    $user->setPreference(UserPreferences::LANGUAGE,,2); 
    // ValidationException because of the cast: Cast::STRING

    $user->removePreference(UserPreferences::LANGUAGE); 
    $user->getPreference(UserPreferences::LANGUAGE,); // 'en' as string
    
    // Or with Enums
    $user->setPreference(UserPreferences::LANGUAGE,"de");
    $user->setPreference(UserPreferences::THEME,"light");
    // get all of type UserPreferences
    $user->getPreferences(UserPreferences::class)
    //get all
    $user->getPreferences()
```

## Casting

> set the cast when creating a Preference
>> PreferenceBuilder::init("language", Cast::STRING)

### Available Casts

INT, FLOAT, STRING, BOOL, ARRAY, TIME, DATE, DATETIME, TIMESTAMP, BACKED_ENUM

### Custom Caster

create a `BackedEnum`, and implement `CastableEnum`

```php
use Illuminate\Contracts\Validation\Rule;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;

enum MyCast: string implements CastableEnum
{
    case TIMEZONE = 'tz';
 
    public function validation(): Rule|array|string
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

## Deprecation plans

### HasValidation for custom Rules
`HasValidation` will be deprecated in version >2.x, since Laravel is Deprecating the Rule Contract

### string names
string `name` for preferences, will be removed in version >2.x, consider using enums, as its the direction this package will take.

### groups
for preferences is deprecated and creating groups will be removed with version 2.x
> groups created with version 1.x will however be still supported. 
> 
> the intended use for groups is internal only in combination with enum names

## Test

`composer test <path>`

`composer coverage`

## Security Vulnerabilities

Please review [our security policy](SECURITY.md) on how to report security vulnerabilities.

## Credits

- [matteoc99](https://github.com/mattoc99)
- [Joel Brown](https://stackoverflow.com/users/659653/joel-brown) for [this](https://stackoverflow.com/questions/10204902/database-design-for-user-settings/10228192#10228192) awesome starting point and initial inspiration

## License

The MIT License (MIT). Please check the [License File](LICENSE) for more information.

# Support target

| Package Version | Laravel Version |
|-----------------|-----------------|
| 1.x             | 10              |
| 2.x             | 11              |
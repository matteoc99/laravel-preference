# Laravel User Preferences

[![Latest Version on Packagist](https://img.shields.io/packagist/v/matteoc99/laravel-preference.svg?style=flat-square)](https://packagist.org/packages/matteoc99/laravel-preference)
[![Total Downloads](https://img.shields.io/packagist/dt/matteoc99/laravel-preference.svg?style=flat-square)](https://packagist.org/packages/matteoc99/laravel-preference)
[![Tests](https://github.com/matteoc99/laravel-preference/actions/workflows/tests.yml/badge.svg)](https://github.com/matteoc99/laravel-preference/actions/workflows/tests.yml)
[![codecov](https://codecov.io/github/matteoc99/laravel-preference/graph/badge.svg?token=GS19E2ORR4)](https://codecov.io/github/matteoc99/laravel-preference)

This Laravel package aims to store and manage user settings/preferences in a simple and scalable manner.

# Table of Contents

* [Features](#features)
    * [Roadmap](#roadmap)
* [Installation](#installation)
* [Usage](#usage)
    * [Concepts](#concepts)
    * [Define your preferences](#define-your-preferences)
    * [Create a Preference](#create-a-preference)
* [Working with preferences](#working-with-preferences)
    * [Examples](#examples)
* [Casting](#casting)
    * [Available Casts](#available-casts)
    * [Custom Caster](#custom-caster)
* [Custom Rules](#custom-rules)
* [Routing](#routing)
    * [Anantomy](#anantomy)
    * [Example](#example)
    * [Actions](#actions)
    * [Middlewares](#middlewares)
* [Upgrade from v1](#upgrade-from-v1)
* [Test](#test)
* [Security Vulnerabilities](#security-vulnerabilities)
* [Credits](#credits)
* [License](#license)
* [Support target](#support-target)

## Features

- Type safe Casting,
    - for example, Cast::BACKED_ENUM expects and always returns an instantiated enum, same goes for all other casts
- Validation
    - basic validation from casting and optionally additional rules
- Extensible
    - (Create your own Validation Rules and Casts)
- Enum support
    - store alle your preferences in one or more enums, to simplify the usage of this package
- Can be used on any number of models
- Api routes
    - work with preferences from a GUI or in addition to backend functionalities
- Authorization checks

### Roadmap

- Additional inbuilt Custom Rules -> v2.x
- Model / Object Casting -> v2.x
- Suggestions are welcome

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
    ], // null -> use defaults
    'routes' => [
        'enabled'     => false, // set true to register routes, more on that later
        'middlewares' => [
            'auth', // general middleware
            'user'=> 'verified', // optional, scoped middleware
            'user.general'=> 'verified' // optional, scoped & grouped middleware
        ],
        'prefix' => 'preferences', 
        'groups'      => [
            //enum class list of preferences
            'general'=>General::class
        ],
        'scopes'=> [
           // as many preferenceable models as you want
            'user' => \Illuminate\Auth\Authenticatable::class
        ]
    ]
```

Run the migrations with:

```bash
php artisan migrate
```

## Usage

### Concepts

Each preference has at least a name and a caster. For additional validation you can add you custom Rule object
> The default caster supports all major primitives, enums, as well as time/datetime/date and timestamp which get
> converted
> with `Carbon/Carbon`

### Define your preferences

Organize them in one or more **string backed** enum.

Each enum gets scoped and does not conflict with other enums with the same case

e.g.

```php
use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;

enum Preferences :string implements PreferenceGroup
{
    case LANGUAGE="language";
    case QUALITY="quality";
    case CONFIG="configuration";
}
enum General :string implements PreferenceGroup
{
    case LANGUAGE="language";
    case THEME="quality";
}
```

### Create a Preference

#### single mode

```php
use Matteoc99\LaravelPreference\Enums\Cast;

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

    // or with casting
    PreferenceBuilder::init(Preferences::LANGUAGE, Cast::BACKED_ENUM)
    ->withDefaultValue(Language::EN)
    ->create()


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
       ];
    }
};

```

## Working with preferences

two things are needed:

- `HasPreferences` trait to access the helper functions
- `PreferenceableModel` Interface to have access to the implementation
    - in particular to `isUserAuthorized`

#### isUserAuthorized

guard function to validate if the currently logged in (if any) user has access to this model
Signature:

- $user the logged in user
- PolicyAction enum: the action the user wants to perform index/get/update/delete

#### Example implementation:

```php
use Matteoc99\LaravelPreference\Contracts\PreferenceableModel;
use Matteoc99\LaravelPreference\Enums\PolicyAction;
use Matteoc99\LaravelPreference\Traits\HasPreferences;

class User extends \Illuminate\Foundation\Auth\User implements PreferenceableModel
{
    use HasPreferences;

    protected $fillable = ['email'];

    public function isUserAuthorized(?Authenticatable $user, PolicyAction $action): bool
    {
        return $user?->id == $this->id ;
    }
}
```

### Examples

```php
    $user->setPreference(Preferences::LANGUAGE,"de");
    $user->getPreference(Preferences::LANGUAGE,); // 'de' as string

    $user->setPreference(Preferences::LANGUAGE,"fr"); 
    // ValidationException because of the rule: ->withRule(new InRule("en","it","de"))
    $user->setPreference(Preferences::LANGUAGE,2); 
    // ValidationException because of the cast: Cast::STRING

    $user->removePreference(Preferences::LANGUAGE); 
    $user->getPreference(Preferences::LANGUAGE); // 'en' as string
    
    // get all of type Preferences,
    $user->getPreferences(Preferences::class)
    // or of type general
    $user->getPreferences(General::class)
    //or all
    $user->getPreferences(): Collection of UserPreferences
```

## Casting

> set the cast when creating a Preference
>> PreferenceBuilder::init(Preferences::LANGUAGE, Cast::STRING)

### Available Casts

INT, FLOAT, STRING, BOOL, ARRAY, TIME, DATE, DATETIME, TIMESTAMP, BACKED_ENUM

### Custom Caster

create a **string backed** enum, and implement `CastableEnum`

```php
use Illuminate\Contracts\Validation\ValidationRule;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;

enum MyCast: string implements CastableEnum
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
}

 PreferenceBuilder::init(Preferences::TIMEZONE,MyCast::TIMEZONE)
 //->...etc

```

## Custom Rules

> rules need to implement `ValidationRule`

> additionally, if your rule requires parameter, extend `DataRule`
> which then will provide the parameters via `getData()` as an array of params

```php
class MyRule extends DataRule
{

    public function message()
    {
        return sprintf("Wrong Timezone, one of: %s expected", implode(", ",$this->getData()));
    }
    
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!Str::startsWith($value, $this->getData())){
            $fail($this->message());
        }
    }
}

 PreferenceBuilder::init("timezone",MyCast::TIMEZONE)
            ->withRule(new MyRule("Europe","Asia"))
```

## Routing

off by default, enable it in the config

### Anantomy:

'Scope': the `PreferenceableModel` Model  
'Group': the `PreferenceGroup` enum

routes then get transformed to:

| Action    | URI                                               | Description                                                 |   
|-----------|---------------------------------------------------|-------------------------------------------------------------|
| GET       | /{prefix}/{scope}/{scope_id}/{group}              | Retrieves all preferences for a given scope and group.      |   
| GET       | /{prefix}/{scope}/{scope_id}/{group}/{preference} | Retrieves a specific preference within the scope and group. |   
| PUT/PATCH | /{prefix}/{scope}/{scope_id}/{group}/{preference} | Updates a specific preference within the scope and group.   |   
| DELETE    | /{prefix}/{scope}/{scope_id}/{group}/{preference} | Deletes a specific preference within the scope and group.   |   

which can all be accessed via the route name: {prefix}.{scope}.{group}.{index/get/update/delete}

#### URI Parameters

`scope_id`: The unique identifier of the scope (e.g., a user's ID).
`preference`: The value of the specific preference enum  (e.g., General::LANGUAGE->value).
`group`: A mapping of group names to their corresponding Enum classes. See config below
`scope`: A mapping of scope names to their corresponding Eloquent model. See config below

### Example

```php
 'routes' => [
        'enabled'     => true, 
        'middlewares' => [
            'auth',
            'user'=> 'verified'
        ],
        'prefix' => 'my_preferences', 
        'groups'      => [
            'general'=>\Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\General::class
            'video'=>\Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\VideoPreferences::class
        ],
        'scopes'=> [
            'user' => \Matteoc99\LaravelPreference\Tests\TestSubjects\Models\User::class
        ]
    ]
```

will result in:

- my_preferences.user.general.index
- my_preferences.user.general.get
- my_preferences.user.general.update
- my_preferences.user.general.delete
- my_preferences.user.video.index
- my_preferences.user.video.get
- my_preferences.user.video.update
- my_preferences.user.video.delete

### Actions

#### INDEX

(my_preferences.user.general.index)   
equivalent to:  `$user->getPreferences(General::class)`

#### GET

(my_preferences.user.general.get)   
equivalent to:  `$user->getPreference(General::{preference})`

#### UPDATE

(my_preferences.user.general.update)   
equivalent to:  `$user->setPreference(General::{preference}, >value<)`  
Payload:
{
'value' => >value<
}

#### DELETE

(my_preferences.user.general.delete)   
equivalent to:  `$user->removePreference(General::{preference})`

### Middlewares

set global or context specific middlewares
in the config file

```php
'middlewares' => [
'auth', //no key => general middleware which gets applied to all routes
'user'=> 'verified', //  scoped middleware only for user routes should you have other preferencable models
'user.general'=> 'verified' // scoped & grouped middleware only for a specific model + enum
],
```

## Upgrade from v1

- implement `PreferenceGroup` in your Preference enums
- implement `PreferenceableModel` in you all Models that want to use preferences
- Switch from `HasValidation` to `ValidationRule`
- Signature changes on the trait: group got removed and name now requires a `PreferenceGroup`
- Builder: setting group got removed and name now expects a `PreferenceGroup` enum

## Test

`composer test`

`composer coverage`

## Security Vulnerabilities

Please review [our security policy](SECURITY.md) on how to report security vulnerabilities.

## Credits

- [matteoc99](https://github.com/mattoc99)
- [Joel Brown](https://stackoverflow.com/users/659653/joel-brown)
  for [this](https://stackoverflow.com/questions/10204902/database-design-for-user-settings/10228192#10228192) awesome
  starting point and initial inspiration

## License

The MIT License (MIT). Please check the [License File](LICENSE) for more information.

## Support target

| Package Version | Laravel Version |
|-----------------|-----------------|
| 1.x             | 10              |
| 2.x             | 10 & 11         |
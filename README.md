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
* [Policies](#policies)
* [Preference Building](#preference-building)
* [Routing](#routing)
    * [Anantomy](#anantomy)
    * [Example](#example-)
    * [Actions](#actions)
    * [Middlewares](#middlewares)
* [Security](#security)
* [Upgrade from v1](#upgrade-from-v1)
* [Test](#test)
* [Security Vulnerabilities](#security-vulnerabilities)
* [Credits](#credits)
* [License](#license)
* [Support target](#support-target)

## Features

- Type safe Casting
- Validation & Authorization
- Extensible (Create your own Validation Rules and Casts)
- Enum support
- Custom Api routes
    - work with preferences from a GUI or in addition to backend functionalities

### Roadmap

- Additional inbuilt Custom Rules -> v2.x
- Allow array of preferenceBuilders in initBuk -> v2.1.1
- Event System -> v2.2
- Suggestions are welcome

## Installation

You can install the package via composer:

```bash
composer require matteoc99/laravel-preference
```

> [!IMPORTANT]
> consider installing also `graham-campbell/security-core:^4.0` to take advantage of xss cleaning.
> see [Security](#security) for more information

### Configuration

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-preference-config"
```

```php
  'db' => [
        'connection' => null, //string: the connection name to use 
        'preferences_table_name'      => 'preferences',
        'user_preferences_table_name' => 'user_preferences',
    ],
    'xss_cleaning' => true, // clean user input for cross site scripting attacks
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

> [!NOTE]
> Consider changing the base table names before running the migrations, if needed

Run the migrations with:

```bash
php artisan migrate
```

## Usage

### Concepts

Each preference has at least a name and a caster. For additional validation you can add you custom Rule object
> [!TIP]
> The default caster supports all major primitives, enums, objects, as well as time/datetime/date and timestamp which
> get converted with `Carbon/Carbon`

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
    case THEME="theme";
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
    PreferenceBuilder::init(Preferences::LANGUAGE, Cast::ENUM)
        ->withDefaultValue(Language::EN)
        ->create()

    // nullable support
    PreferenceBuilder::init(Preferences::LANGUAGE, Cast::ENUM)
        ->withDefaultValue(null)
        ->nullable()
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

        PreferenceBuilder::initBulk($this->preferences(),
        true // nullable for the whole Bulk
        );
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
            ['name' => Preferences::CONFIGURATION, 
                'nullable' => true // or nullable for only one configuration
            ],
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

> [!NOTE]
> this is just the bare minimum regarding Authorization.  
> For more fine-grained authorization checks refer to [Policies](#policies)

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
    $user->getPreference(Preferences::LANGUAGE); // 'de' as string

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

set the cast when creating a Preference

```php 
PreferenceBuilder::init(Preferences::LANGUAGE, Cast::STRING)
```

### Available Casts

| Cast        | Explanation                                                                       |
|-------------|-----------------------------------------------------------------------------------|
| INT         | Converts and Validates a value to be an integer.                                  |
| FLOAT       | Converts and Validates a value to be a floating-point number.                     |
| STRING      | Converts and Validates a value to be a string.                                    |
| BOOL        | Converts and Validates a value to be a boolean (regards non-empty as `true`).     |
| ARRAY       | Converts and Validates a value to be an array.                                    |
| BACKED_ENUM | Ensures the value is a BackedEnum type. Useful for enums with underlying values.  |
| ENUM        | Ensures the value is a UnitEnum type. Useful for enums without underlying values. |
| OBJECT      | Ensures that the value is an object.                                              |
| NONE        | No casting is performed. Returns the value as-is.                                 |

| Date-Casts | Explanation                                                                                                  |
|------------|--------------------------------------------------------------------------------------------------------------|
|            | Converts a value using Carbon::parse, and always return a Carbon instance. <br/> Validation is Cast-Specific |
| DATE       | sets the time to be `00:00`.                                                                                 |
| TIME       | Always uses the current date, setting only the time                                                          |
| DATETIME   | with both date and time(optionally).                                                                         |
| TIMESTAMP  | allows a string/int timestamp or a carbon instance                                                           |

### Custom Caster

implement `CastableEnum`

> [!IMPORTANT]
> The custom caster needs to be a **string backed** enum

#### Example:

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

implement `ValidationRule`

#### Example:

```php
class MyRule implements ValidationRule
{

    protected array $data;

    public function __construct(...$data)
    {
        $this->data = $data;
    }

    public function message()
    {
        return sprintf("Wrong Timezone, one of: %s expected", implode(", ",$this->data));
    }
    
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!Str::startsWith($value, $this->data)){
            $fail($this->message());
        }
    }
}

 PreferenceBuilder::init("timezone",MyCast::TIMEZONE)
            ->withRule(new MyRule("Europe","Asia"))
```

## Policies

each preference can have a Policy, should [isUserAuthorized](#isuserauthorized) not be enough for your usecase

### Creating policies

implement `PreferencePolicy` and the 4 methods defined by the contract

| parameter                   | description                                                |   
|-----------------------------|------------------------------------------------------------|
| Authenticatable $user       | the currently logged in user, if any                       |
| PreferenceableModel $model  | the model on which you are trying to modify the preference |
| PreferenceGroup $preference | the preference enum in question                            |

### Adding policies

````php
    PreferenceBuilder::init(Preferences::LANGUAGE)
        ->withPolicy(new MyPolicy())
        ->updateOrCreate()


    PreferenceBuilder::initBulk([
        'name' => Preferences::LANGUAGE,
        'policy' => new MyPolicy()
     ]);

````

## Preference Building

| Single-Mode                         | Bulk-Mode (array-keys)                      | Constrains                                                                | Description                                                                                                                                                       |
|-------------------------------------|---------------------------------------------|---------------------------------------------------------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| init(>name<,>cast<)                 | ```["name"=> >name<]```                     | \>name< = instanceof PreferenceGroup                                      | Unique identifier for the preference                                                                                                                              |
| init(>name<,>cast<)                 | ```["cast"=> >cast<]```                     | \>cast< = instanceof CastableEnum                                         | Caster to translate the value between all different scenarios. Currently: Api-calls as well as saving to and retrieving fron the DB                               |
| nullable(>nullable<)                | ```["nullable"=> >nullable<]```             | \>nullable< = bool                                                        | Whether the default value can be null and if the preference can be set to null                                                                                    |
| withDefaultValue(>default_value<)   | ```["default_value"=> >default_value<]```   | \>default_value< = mixed, but must comply with the cast & validationRule  | Initial value for this preference                                                                                                                                 |
| withDescription(>description<)      | ```["description"=> >description<]```       | \>description< = string                                                   | Legacy code from v1.x has no actual use as of now                                                                                                                 |
| withPolicy(>policy<)                | ```["policy"=> >policy<]```                 | \>policy< = instanceof PreferencePolicy                                   | Authorize actions such as update/delete etc. on certain preferences.                                                                                              |
| withRule(>rule<)                    | ```["rule"=> >rule<]```                     | \>rule< = instanceof ValidationRule                                       | Additional validation Rule, to validate values before setting them                                                                                                |
| setAllowedClasses(>allowed_values<) | ```["allowed_values"=> >allowed_values<]``` | \>allowed_values< = array of string classes. For non Primitive Casts only | Current use-cases: <br/> - restrict classes of enum or object that can be set to this preference<br/> - reconstruct the original class when sending data via api. |

## Routing

off by default, enable it in the config

> [!WARNING]
> **(Current) limitation**: it's not possible to set object casts via API

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

### Example:

```php
 'routes' => [
        'enabled'     => true, 
        'middlewares' => [
            'auth',
            'user'=> 'verified'
        ],
        'prefix' => 'custom_prefix', 
        'groups'      => [
            'general'=>\Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\General::class
            'video'=>\Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\VideoPreferences::class
        ],
        'scopes'=> [
            'user' => \Matteoc99\LaravelPreference\Tests\TestSubjects\Models\User::class
        ]
    ]
```

will result in the following **route names**:

- custom_prefix.user.general.index
- custom_prefix.user.general.get
- custom_prefix.user.general.update
- custom_prefix.user.general.delete
- custom_prefix.user.video.index
- custom_prefix.user.video.get
- custom_prefix.user.video.update
- custom_prefix.user.video.delete

### Actions

> [!NOTE]
> Examples are with scope `user` and group `general`


#### INDEX


- Route Name: custom_prefix.user.general.index
- Url params: `scope_id`
- Equivalent to: `$user->getPreferences(General::class)`
- Http method: GET
- Endpoint: 'https://your.domain/custom_prefix/user/{scope_id}/general'


#### GET

- Route Name: custom_prefix.user.general.get
- Url params: `scope_id`,`preference`
- Equivalent to: `$user->getPreference(General::{preference})`
- Http method: GET
- Endpoint: https://your.domain/custom_prefix/user/{scope_id}/general/{preference}

#### UPDATE

- Route Name: custom_prefix.user.general.update   
- Url params: `scope_id`,`preference`   
- Equivalent to:  `$user->setPreference(General::{preference}, >value<)`  
- Http method: PATCH/PUT
- Endpoint: https://your.domain/custom_prefix/user/{scope_id}/general/{preference}
- Payload:
`
{
  "value": >value<
}
`



##### Enum Patching

When creating your enum preference, add `setAllowedClasses` containing the possible enums to reconstruct the value
> [!CAUTION]
> if multiple cases are shared between enums, the first match is taken   

then, when sending the value it varies:

- BackedEnum: send the value or the case
- UnitEnum: send the case

Example:

```php
enum Theme
{
    case LIGHT;
    case DARK;
}
curl -X PATCH 'https://your.domain/custom_prefix/user/{scope_id}/general/{preference}' \
    -d '{"value": "DARK"}'
```

#### DELETE

- Route Name: (custom_prefix.user.general.delete)
- Url params: `scope_id`,`preference`
- Equivalent to:  `$user->removePreference(General::{preference})`
- Http method: DELETE
- Endpoint: https://your.domain/custom_prefix/user/{scope_id}/general/{preference}

### Middlewares

set global or context specific middlewares
in the config file

```php
'middlewares' => [
'web', // required for Auth::user() and policies
'auth', //no key => general middleware which gets applied to all routes
'user'=> 'verified', //  scoped middleware only for user routes should you have other preferencable models
'user.general'=> 'verified' // scoped & grouped middleware only for a specific model + enum
],
```

**known Issues**: without the web middleware, you won't have access to the user via the Auth facade
since it's set by the middleware. Looking into an alternative

## Security

XSS cleaning is only performed on user facing api calls.
this can be disabled, if not required, with the config: `user_preference.xss_cleaning`

When setting preferences directly via `setPreference`
this cleaning step is assumed to have already been performed, if necessary.

Consider installing [Security-Core](https://github.com/GrahamCampbell/Security-Core) to make use of this feature

## Upgrade from v1

- implement `PreferenceGroup` in your Preference enums
- implement `PreferenceableModel` in you all Models that want to use preferences
- Switch from `HasValidation` to `ValidationRule`
- Signature changes on the trait: group got removed and name now requires a `PreferenceGroup`
- Builder: setting group got removed and name now expects a `PreferenceGroup` enum
- `DataRule` has been removed, add a constructor to get you own, tailored, params
- database serialization incompatibilities will require you to rerun your Preference migrations
    - [single mode](#single-mode): make sure to use `updateOrCreate`,
      e.g ` PreferenceBuilder::init(VideoPreferences::QUALITY)->updateOrCreate();`
    - [bulk mode](#bulk-mode): initBulk as usual, as it works with upsert

## Test

`composer test`

`composer coverage`

#### Test the pipeline locally

check out [act](https://github.com/nektos/act)
install it via [gh](https://nektosact.com/installation/gh.html)

then run: `composer pipeline`

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
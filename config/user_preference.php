<?php

return [
    'db' => [
        'connection' => null,
        'preferences_table_name' => 'preferences',
        'user_preferences_table_name' => 'users_preferences',
    ],
    'xss_cleaning' => true,
    'routes' => [
        'enabled' => false,
        'middlewares' => [
            'web', // required for Auth::user() and policies
            'auth', // general middleware
            'user' => 'verified', // optional, scoped middleware
            'user.general' => 'verified', // optional, scoped & grouped middleware
        ],
        'prefix' => 'preferences',
        'groups' => [
            //enum class list of preferences
            //'general'=>General::class
        ],
        'scopes' => [
            // as many preferenceable models as you want
            'user' => \Illuminate\Auth\Authenticatable::class,
        ],
    ],
];

<?php

return [
    'db'     => [
        'connection' => null,
    ],
    'routes' => [
        'enabled'     => false,
        'middlewares' => [
            'auth', // general middleware
            'user'=> 'verified', // optional, scoped middleware
            'user.general'=> 'verified' // optional, scoped & grouped middleware
        ],
        'prefix' => 'preferences',
        'groups'      => [
            //enum class list of preferences
            //'general'=>General::class
        ],
        'scopes'=> [
           // as many preferenceable models as you want
            'user' => \Illuminate\Auth\Authenticatable::class
        ]
    ]
];
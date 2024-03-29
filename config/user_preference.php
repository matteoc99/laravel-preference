<?php

return [
    'db'     => [
        'connection' => null,
    ],
    'routes' => [
        'enabled'     => false,
        'middlewares' => [
            'auth', // general middleware
            'user'=> 'verified' // optional, scoped middleware
        ],
        'prefix' => 'preferences',
        'groups'      => [
            //enum class list of preferences
            //'general'=>General::class
        ],
        'scopes'=> [
           // as many preferencable models as you want
            'user' => \Illuminate\Auth\Authenticatable::class
        ]
    ]
];
<?php

return [
    'default' => env('DB_CONNECTION', 'pgsql'),
    'migrations'  => 'migrations',
    'fetch' => PDO::FETCH_CLASS,

    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
        'master' => [
            'driver' => 'pgsql',
            'host' => env('DBM_HOST', '127.0.0.1'),
            'port' => env('DBM_PORT', '5432'),
            'database' => env('DBM_DATABASE', 'forge'),
            'username' => env('DBM_USERNAME', 'forge'),
            'password' => env('DBM_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => 'public',
            'sslmode' => 'prefer',
        ],
    ],

    'redis' => [
        'client'  => 'predis',
        'cluster' => env('REDIS_CLUSTER', false),
        'default' => [
            'host'     => env('REDIS_HOST', 'redis'),
            'password' => env('REDIS_PASSWORD', null),
            'port'     => env('REDIS_PORT', 6379),
            'database' => env('REDIS_DATABASE', 1),
        ],
    ],
];
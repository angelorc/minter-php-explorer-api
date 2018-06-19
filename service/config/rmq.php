<?php

return [
    'explorer' => [
        'general' => [
            'host' => env('RMQ_HOST', 'localhost'),
            'port' => env('RMQ_PORT', 5672),
            'user' => env('RMQ_USER', 'guest'),
            'password' => env('RMQ_PASSWORD', 'guest'),
            'vhost' => env('RMQ_VHOST', '/'),
            'params' => [
                'insist' => false,
                'login_method' => 'AMQPLAIN',
                'login_response' => null,
                'locale' => 'en_US',
                'connection_timeout' => 3.0,
                'read_write_timeout' => 25.0,
                'context' => null,
                'keepalive' => false,
                'heartbeat' => 12,
            ],
        ],
        'queues' => [
            App\Console\Commands\BlocksQueueWorkerCommand::QUEUE_NAME,
        ],
    ],
];

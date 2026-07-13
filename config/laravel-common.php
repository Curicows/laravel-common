<?php

declare(strict_types=1);

return [
    'commands' => [
        'generator' => [
            'enabled' => env('LARAVEL_COMMON_COMMANDS_GENERATOR_ENABLED', true),
        ],
    ],

    'stubs' => [
        'path' => base_path('stubs/curicows'),
        'generator' => [
            'controller' => ['path' => 'app/Http/Controllers', 'namespace' => 'Http/Controllers'],
            'repository' => ['path' => 'app/Http/Repositories', 'namespace' => 'Http/Repositories'],
            'service' => ['path' => 'app/Services', 'namespace' => 'Services'],
            'dto' => ['path' => 'app/Http/Dtos', 'namespace' => 'Http/Dtos'],
            'policy' => ['path' => 'app/Policies', 'namespace' => 'Policies'],
            'model' => ['path' => 'app/Models', 'namespace' => 'Models'],
            'view' => ['path' => 'resources/views', 'namespace' => ''],
        ],
    ],

    'subscribers' => [
        'authenticate_queued_user' => env('LARAVEL_COMMON_SUBSCRIBERS_AUTHENTICATE_QUEUED_USER', true),
    ],
];

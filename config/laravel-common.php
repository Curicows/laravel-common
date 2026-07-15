<?php

declare(strict_types=1);

use Curicows\LaravelCommon\Http\Dtos\Auth\TwoFactor\UserTwoFactorDto;
use Curicows\LaravelCommon\Services\Auth\TwoFactor\EmailTwoFactorMethod;
use Curicows\LaravelCommon\Services\Auth\TwoFactor\OtpTwoFactorMethod;

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

    'models' => [
        'user' => null,
        'user_two_factor_dto' => UserTwoFactorDto::class,
        'user_google_oauth_dto' => null,
    ],

    'two_factor' => [
        'bad_request_exception' => null,
        'methods' => [
            EmailTwoFactorMethod::class,
            OtpTwoFactorMethod::class,
        ],
        'email' => [
            'mailable' => null,
        ],
    ],
];

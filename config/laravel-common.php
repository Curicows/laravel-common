<?php

declare(strict_types=1);

return [
    'subscribers' => [
        'authenticate_queued_user' => env('LARAVEL_COMMON_SUBSCRIBERS_AUTHENTICATE_QUEUED_USER', true),
    ],
];

<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Fixtures;

use MrPunyapal\LaravelAuthJobs\Contracts\HasContextKeys;

final class TestContextKeys implements HasContextKeys
{
    public static function authIdKey(): string
    {
        return 'auth_id';
    }

    public static function authGuardKey(): string
    {
        return 'auth_guard';
    }
}

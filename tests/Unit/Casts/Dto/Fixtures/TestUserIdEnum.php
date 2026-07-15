<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Casts\Dto\Fixtures;

enum TestUserIdEnum: string
{
    case Primary = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';
}

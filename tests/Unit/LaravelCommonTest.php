<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit;

use Curicows\LaravelCommon\LaravelCommon;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LaravelCommon::class)]
class LaravelCommonTest extends TestCase
{
    public function test_package_name_is_available(): void
    {
        self::assertSame('laravel-common', LaravelCommon::packageName());
    }
}

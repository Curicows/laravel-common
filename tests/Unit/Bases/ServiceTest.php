<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Bases;

use Curicows\LaravelCommon\Bases\Service;
use Curicows\LaravelCommon\Tests\Fixtures\SampleService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Service::class)]
class ServiceTest extends TestCase
{
    public function test_service_can_be_extended(): void
    {
        self::assertInstanceOf(Service::class, new SampleService);
    }
}

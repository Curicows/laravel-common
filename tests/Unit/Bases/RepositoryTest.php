<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Bases;

use Curicows\LaravelCommon\Bases\Repository;
use Curicows\LaravelCommon\Tests\Fixtures\SampleRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Repository::class)]
class RepositoryTest extends TestCase
{
    public function test_repository_can_be_extended(): void
    {
        self::assertInstanceOf(Repository::class, new SampleRepository);
    }
}

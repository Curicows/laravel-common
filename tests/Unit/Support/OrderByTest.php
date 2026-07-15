<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Support;

use Curicows\LaravelCommon\Support\OrderBy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OrderBy::class)]
class OrderByTest extends TestCase
{
    public function test_order_by_uses_id_ascending_by_default(): void
    {
        $orderBy = new OrderBy;

        self::assertSame('id', $orderBy->column);
        self::assertSame('asc', $orderBy->direction);
    }

    public function test_order_by_accepts_column_and_direction(): void
    {
        $orderBy = new OrderBy(column: 'name', direction: 'desc');

        self::assertSame('name', $orderBy->column);
        self::assertSame('desc', $orderBy->direction);
    }
}

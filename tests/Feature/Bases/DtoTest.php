<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Feature\Bases;

use Curicows\LaravelCommon\Bases\Dto;
use Curicows\LaravelCommon\Tests\Fixtures\SampleDto;
use Curicows\LaravelCommon\Tests\TestCase;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;

#[CoversClass(Dto::class)]
class DtoTest extends TestCase
{
    public function test_data_collects_items_into_data_collection(): void
    {
        $collection = SampleDto::data([
            ['id' => 1, 'name' => 'First'],
            ['id' => 2, 'name' => 'Second'],
        ]);

        self::assertInstanceOf(DataCollection::class, $collection);
        self::assertCount(2, $collection);
        self::assertContainsOnlyInstancesOf(SampleDto::class, $collection);
    }

    public function test_paginate_collects_items_into_paginated_data_collection(): void
    {
        $paginator = new LengthAwarePaginator(
            items: [
                ['id' => 1, 'name' => 'First'],
                ['id' => 2, 'name' => 'Second'],
            ],
            total: 2,
            perPage: 2,
            currentPage: 1,
        );

        $collection = SampleDto::paginate($paginator);

        self::assertInstanceOf(PaginatedDataCollection::class, $collection);
        self::assertCount(2, $collection);
        self::assertContainsOnlyInstancesOf(SampleDto::class, $collection);
    }
}

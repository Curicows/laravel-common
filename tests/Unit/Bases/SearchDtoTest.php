<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Bases;

use Curicows\LaravelCommon\Bases\Dto;
use Curicows\LaravelCommon\Bases\SearchDto;
use Curicows\LaravelCommon\Tests\Fixtures\SampleSearchDto;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SearchDto::class)]
class SearchDtoTest extends TestCase
{
    public function test_search_dto_extends_base_dto(): void
    {
        $dto = new SampleSearchDto(term: 'city');

        self::assertInstanceOf(SearchDto::class, $dto);
        self::assertInstanceOf(Dto::class, $dto);
        self::assertSame('city', $dto->term);
    }
}

<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Http\Dtos;

use Curicows\LaravelCommon\Http\Dtos\UrlResponseDto;
use Curicows\LaravelCommon\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UrlResponseDto::class)]
class UrlResponseDtoTest extends TestCase
{
    public function test_stores_url(): void
    {
        $dto = new UrlResponseDto('https://curicows.test/files');

        $this->assertSame('https://curicows.test/files', $dto->url);
    }
}

<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Http\Dtos;

use Curicows\LaravelCommon\Http\Dtos\SuccessDto;
use Curicows\LaravelCommon\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SuccessDto::class)]
class SuccessDtoTest extends TestCase
{
    public function test_defaults_to_success_true(): void
    {
        $dto = new SuccessDto;

        $this->assertTrue($dto->success);
    }

    public function test_can_represent_false_success(): void
    {
        $dto = new SuccessDto(false);

        $this->assertFalse($dto->success);
    }
}

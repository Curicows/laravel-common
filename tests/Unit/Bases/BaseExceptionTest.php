<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Bases;

use Curicows\LaravelCommon\Bases\BaseException;
use Curicows\LaravelCommon\Http\Dtos\ErrorDto;
use Curicows\LaravelCommon\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BaseException::class)]
class BaseExceptionTest extends TestCase
{
    public function test_report_defaults_to_true(): void
    {
        $exception = new TestBaseException('Broken', 422);

        $this->assertTrue($exception->report());
    }

    public function test_render_returns_error_dto(): void
    {
        $exception = new TestBaseException('Broken', 422);

        $result = $exception->render();

        $this->assertInstanceOf(ErrorDto::class, $result);
        $this->assertSame('Broken', $result->message);
        $this->assertSame(422, $result->statusCode);
        $this->assertSame([], $result->errors);
    }
}

class TestBaseException extends BaseException {}

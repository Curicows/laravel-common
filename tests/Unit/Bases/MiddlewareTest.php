<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Bases;

use Curicows\LaravelCommon\Bases\Middleware;
use Curicows\LaravelCommon\Tests\Fixtures\SampleMiddleware;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(Middleware::class)]
class MiddlewareTest extends TestCase
{
    public function test_middleware_can_delegate_to_next_handler(): void
    {
        $middleware = new SampleMiddleware;
        $response = new Response('ok');

        $actual = $middleware->handle(Request::create('/'), fn (): Response => $response);

        self::assertSame($response, $actual);
    }
}

<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Feature\Bases;

use Curicows\LaravelCommon\Bases\Controller;
use Curicows\LaravelCommon\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Controller::class)]
class ControllerTest extends TestCase
{
    public function test_no_content_returns_empty_204_response(): void
    {
        $response = ConcreteController::noContent();

        self::assertSame(204, $response->getStatusCode());
        self::assertSame('', $response->getContent());
    }
}

final class ConcreteController extends Controller {}

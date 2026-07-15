<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Casts;

use Curicows\LaravelCommon\Casts\UppercaseCast;
use Curicows\LaravelCommon\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(UppercaseCast::class)]
class UppercaseCastTest extends TestCase
{
    public function test_get_method(): void
    {
        $cast = new UppercaseCast;

        $this->assertEquals('TESTVALUE', $cast->get(
            model: new class extends Model {},
            key: 'testKey',
            value: 'testValue',
            attributes: []
        ));
    }

    public function test_set_method(): void
    {
        $cast = new UppercaseCast;

        $this->assertEquals('TESTVALUE', $cast->set(
            model: new class extends Model {},
            key: 'testKey',
            value: 'testValue',
            attributes: []
        ));
    }
}

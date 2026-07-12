<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Fixtures;

use Curicows\LaravelCommon\Bases\Dto;

final class SampleDto extends Dto
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
    ) {}
}

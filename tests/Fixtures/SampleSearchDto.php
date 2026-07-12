<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Fixtures;

use Curicows\LaravelCommon\Bases\SearchDto;

final class SampleSearchDto extends SearchDto
{
    public function __construct(
        public readonly ?string $term = null,
    ) {}
}

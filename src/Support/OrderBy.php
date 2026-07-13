<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Support;

use JetBrains\PhpStorm\ExpectedValues;

readonly class OrderBy
{
    public function __construct(
        public string $column = 'id',
        #[ExpectedValues(values: ['asc', 'desc'])]
        public string $direction = 'asc',
    ) {}
}

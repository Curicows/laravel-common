<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Http\Dtos;

use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Data;

/**
 * @template TValue
 */
class ApiDataDto extends Data
{
    public function __construct(
        /** @var Collection<int, TValue>|TValue[] $data */
        public readonly array|Collection $data
    ) {}
}

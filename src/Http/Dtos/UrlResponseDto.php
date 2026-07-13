<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Http\Dtos;

use Curicows\LaravelCommon\Bases\Dto;

class UrlResponseDto extends Dto
{
    public function __construct(
        public readonly string $url,
    ) {}
}

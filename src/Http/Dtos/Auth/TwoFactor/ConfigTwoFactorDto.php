<?php

namespace Curicows\LaravelCommon\Http\Dtos\Auth\TwoFactor;

use Spatie\LaravelData\Data;

class ConfigTwoFactorDto extends Data
{
    public function __construct(
        public readonly string $secretKey,
        public readonly string $code,
    ) {}
}

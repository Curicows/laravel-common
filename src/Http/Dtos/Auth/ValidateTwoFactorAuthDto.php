<?php

namespace Curicows\LaravelCommon\Http\Dtos\Auth;

use Spatie\LaravelData\Data;

class ValidateTwoFactorAuthDto extends Data
{
    public function __construct(
        public readonly string $code
    ) {}
}

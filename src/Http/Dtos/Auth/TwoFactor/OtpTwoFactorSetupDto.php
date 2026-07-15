<?php

namespace Curicows\LaravelCommon\Http\Dtos\Auth\TwoFactor;

use Curicows\LaravelCommon\Bases\Dto;

class OtpTwoFactorSetupDto extends Dto
{
    public function __construct(
        public readonly string $secretKey,
        public readonly string $qrCode,
    ) {}
}

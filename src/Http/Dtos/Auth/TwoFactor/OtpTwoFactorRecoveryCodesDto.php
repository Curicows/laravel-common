<?php

namespace Curicows\LaravelCommon\Http\Dtos\Auth\TwoFactor;

use Curicows\LaravelCommon\Bases\Dto;

class OtpTwoFactorRecoveryCodesDto extends Dto
{
    /**
     * @param  array<int, string>  $recoveryCodes
     */
    public function __construct(
        public readonly array $recoveryCodes,
    ) {}
}

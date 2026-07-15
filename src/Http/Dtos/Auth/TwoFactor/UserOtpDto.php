<?php

namespace Curicows\LaravelCommon\Http\Dtos\Auth\TwoFactor;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class UserOtpDto extends Data
{
    public function __construct(
        public string $secret,
        /** @var array<string> */
        public array $recoveryCodes,
        public Carbon $configuredAt,
    ) {}
}

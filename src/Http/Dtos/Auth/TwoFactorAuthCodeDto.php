<?php

namespace Curicows\LaravelCommon\Http\Dtos\Auth;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class TwoFactorAuthCodeDto extends Data
{
    public function __construct(
        public readonly string $code,
        public readonly Carbon $createdAt,
    ) {}

    public function expiresAt(): Carbon
    {
        return $this->createdAt->clone()->addMinutes(30);
    }

    public function expired(): bool
    {
        return now() > $this->expiresAt();
    }

    public function verifyCode(string $code): bool
    {
        return $this->code === $code;
    }
}

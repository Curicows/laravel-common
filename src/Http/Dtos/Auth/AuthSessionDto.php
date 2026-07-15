<?php

namespace Curicows\LaravelCommon\Http\Dtos\Auth;

use Carbon\Carbon;
use Curicows\LaravelCommon\Enums\Auth\TwoFactorAuthTypeEnum;
use Curicows\LaravelCommon\Models\User;
use Spatie\LaravelData\Data;

class AuthSessionDto extends Data
{
    public function __construct(
        public readonly User $user,
        public readonly Carbon $loggedIn,
        public ?Carbon $sudoAt,
        public ?TwoFactorAuthCodeDto $twoFactorCode,
        public bool $emailTwoFactorEnabled = false,
        public ?TwoFactorAuthTypeEnum $activeTwoFactorMethod = null,
        /** @var array<int, TwoFactorAuthTypeEnum> */
        public array $availableTwoFactorMethods = [],
    ) {}

    public function sudoUntil(): ?Carbon
    {
        return $this->sudoAt?->clone()->addMinutes(30);
    }

    public function isSudo(): bool
    {
        return $this->sudoUntil()?->isFuture() ?? false;
    }

    public function loginExpired(): bool
    {
        return now() > $this->loggedIn->clone()->addHours(8);
    }

    public function hasMailTwoFactor(): TwoFactorAuthTypeEnum
    {
        return $this->activeTwoFactorMethod
            ?? ($this->twoFactorCode === null ? TwoFactorAuthTypeEnum::Otp : TwoFactorAuthTypeEnum::Email);
    }

    public function hasEmailTwoFactor(): bool
    {
        return $this->hasTwoFactorMethod(TwoFactorAuthTypeEnum::Email);
    }

    public function hasOtp(): bool
    {
        return $this->hasTwoFactorMethod(TwoFactorAuthTypeEnum::Otp);
    }

    public function hasTwoFactorMethod(TwoFactorAuthTypeEnum $method): bool
    {
        return in_array($method, $this->availableTwoFactorMethods, true);
    }
}

<?php

namespace Curicows\LaravelCommon\Http\Dtos\Auth;

use Carbon\Carbon;
use Curicows\LaravelCommon\Enums\Auth\TwoFactorAuthTypeEnum;
use Spatie\LaravelData\Data;

class TwoFactorChallengeDto extends Data
{
    public function __construct(
        public readonly bool $twoFactorRequired,
        public readonly string $type,
        /** @var array<int, string> */
        public readonly array $types,
        public readonly ?Carbon $expiresAt = null,
    ) {}

    public static function fromSession(AuthSessionDto $session): self
    {
        return new self(
            twoFactorRequired: true,
            type: strtolower($session->hasMailTwoFactor()->name),
            types: self::typesFromSession($session),
            expiresAt: $session->hasMailTwoFactor() === TwoFactorAuthTypeEnum::Email
                ? $session->twoFactorCode?->expiresAt()
                : $session->loggedIn->clone()->addMinutes(30),
        );
    }

    /**
     * @return array<int, string>
     */
    private static function typesFromSession(AuthSessionDto $session): array
    {
        return array_values(array_filter([
            $session->hasEmailTwoFactor() ? strtolower(TwoFactorAuthTypeEnum::Email->name) : null,
            $session->hasOtp() ? strtolower(TwoFactorAuthTypeEnum::Otp->name) : null,
        ]));
    }
}

<?php

namespace Curicows\LaravelCommon\Services\Auth\TwoFactor;

use Carbon\Carbon;
use Curicows\LaravelCommon\Contracts\Auth\TwoFactorMethod;
use Curicows\LaravelCommon\Enums\Auth\TwoFactorAuthTypeEnum;
use Curicows\LaravelCommon\Http\Dtos\Auth\AuthSessionDto;
use Curicows\LaravelCommon\Http\Dtos\Auth\TwoFactor\TwoFactorMethodConfigDto;
use Curicows\LaravelCommon\Models\User;
use Google2FA;

class OtpTwoFactorMethod implements TwoFactorMethod
{
    public function type(): TwoFactorAuthTypeEnum
    {
        return TwoFactorAuthTypeEnum::Otp;
    }

    public function isConfiguredFor(User $user): bool
    {
        return $this->configFor($user)?->otpConfig() !== null;
    }

    public function startChallenge(User $user, AuthSessionDto $session): AuthSessionDto
    {
        return $session;
    }

    public function verify(User $user, string $code, AuthSessionDto $session): bool
    {
        $otp = $this->configFor($user)?->otpConfig();

        return $otp !== null && Google2FA::verifyKey($otp->secret, $code);
    }

    public function verifyRecoveryCode(User $user, string $recoveryCode): bool
    {
        $twoFactor = $user->twoFactor();
        $config = $twoFactor->method($this->type());
        $otp = $config?->otpConfig();

        if (! $config || ! $otp) {
            return false;
        }

        $foundCode = array_find($otp->recoveryCodes, fn ($code) => $code === $recoveryCode);

        if (! $foundCode) {
            return false;
        }

        $otp->recoveryCodes = array_values(array_filter(
            $otp->recoveryCodes,
            fn ($code) => $code !== $recoveryCode,
        ));

        $user->two_factor = $twoFactor->withMethod(TwoFactorMethodConfigDto::otp($otp));
        $user->save();

        return true;
    }

    public function expiresAt(AuthSessionDto $session): ?Carbon
    {
        return $session->loggedIn->clone()->addMinutes(30);
    }

    public function configFor(User $user): ?TwoFactorMethodConfigDto
    {
        return $user->twoFactor()->method($this->type());
    }
}

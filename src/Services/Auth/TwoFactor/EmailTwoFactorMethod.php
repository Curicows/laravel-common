<?php

namespace Curicows\LaravelCommon\Services\Auth\TwoFactor;

use Carbon\Carbon;
use Curicows\LaravelCommon\Contracts\Auth\TwoFactorMethod;
use Curicows\LaravelCommon\Enums\Auth\TwoFactorAuthTypeEnum;
use Curicows\LaravelCommon\Http\Dtos\Auth\AuthSessionDto;
use Curicows\LaravelCommon\Http\Dtos\Auth\TwoFactor\TwoFactorMethodConfigDto;
use Curicows\LaravelCommon\Http\Dtos\Auth\TwoFactorAuthCodeDto;
use Curicows\LaravelCommon\Models\User;
use Illuminate\Support\Facades\Mail;

class EmailTwoFactorMethod implements TwoFactorMethod
{
    public function type(): TwoFactorAuthTypeEnum
    {
        return TwoFactorAuthTypeEnum::Email;
    }

    public function isConfiguredFor(User $user): bool
    {
        return (bool) config('auth.2fa.email.enabled', true)
            && $user->twoFactor()->hasMethod($this->type());
    }

    public function startChallenge(User $user, AuthSessionDto $session): AuthSessionDto
    {
        $session->twoFactorCode = $this->generateCode();

        $mailable = config('laravel-common.two_factor.email.mailable');

        if (is_string($mailable) && class_exists($mailable)) {
            Mail::to($user->email)->queue(new $mailable($user, $session->twoFactorCode->code));
        }

        return $session;
    }

    public function verify(User $user, string $code, AuthSessionDto $session): bool
    {
        return $session->twoFactorCode !== null
            && ! $session->twoFactorCode->expired()
            && $session->twoFactorCode->verifyCode($code);
    }

    public function expiresAt(AuthSessionDto $session): ?Carbon
    {
        return $session->twoFactorCode?->expiresAt();
    }

    public function configFor(User $user): ?TwoFactorMethodConfigDto
    {
        return $user->twoFactor()->method($this->type());
    }

    private function generateCode(): TwoFactorAuthCodeDto
    {
        return new TwoFactorAuthCodeDto(
            code: str_pad((string) mt_rand(100000, 999999), 6, '0', STR_PAD_LEFT),
            createdAt: now(),
        );
    }
}

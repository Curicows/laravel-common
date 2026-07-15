<?php

namespace Curicows\LaravelCommon\Contracts\Auth;

use Carbon\Carbon;
use Curicows\LaravelCommon\Enums\Auth\TwoFactorAuthTypeEnum;
use Curicows\LaravelCommon\Http\Dtos\Auth\AuthSessionDto;
use Curicows\LaravelCommon\Http\Dtos\Auth\TwoFactor\TwoFactorMethodConfigDto;
use Curicows\LaravelCommon\Models\User;

interface TwoFactorMethod
{
    public function type(): TwoFactorAuthTypeEnum;

    public function isConfiguredFor(User $user): bool;

    public function startChallenge(User $user, AuthSessionDto $session): AuthSessionDto;

    public function verify(User $user, string $code, AuthSessionDto $session): bool;

    public function expiresAt(AuthSessionDto $session): ?Carbon;

    public function configFor(User $user): ?TwoFactorMethodConfigDto;
}

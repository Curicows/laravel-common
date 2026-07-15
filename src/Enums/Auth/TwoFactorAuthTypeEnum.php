<?php

namespace Curicows\LaravelCommon\Enums\Auth;

enum TwoFactorAuthTypeEnum: int
{
    case Email = 0;
    case Otp = 1;
}

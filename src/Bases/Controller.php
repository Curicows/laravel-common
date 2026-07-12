<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Bases;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Response;

abstract class Controller
{
    use AuthorizesRequests;

    public static function noContent(): Response
    {
        return response()->noContent();
    }
}

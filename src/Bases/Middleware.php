<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Bases;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class Middleware
{
    abstract public function handle(Request $request, Closure $next): Response;
}

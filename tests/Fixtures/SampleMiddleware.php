<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Fixtures;

use Closure;
use Curicows\LaravelCommon\Bases\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class SampleMiddleware extends Middleware
{
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}

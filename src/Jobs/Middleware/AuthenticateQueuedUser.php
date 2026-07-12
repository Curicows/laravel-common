<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Jobs\Middleware;

use Closure;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use MrPunyapal\LaravelAuthJobs\Contracts\HasContextKeys;

class AuthenticateQueuedUser
{
    public function handle(object $job, Closure $next): void
    {
        $contextKeys = resolve(HasContextKeys::class);

        $guard = Context::getHidden($contextKeys::authGuardKey());
        $id = Context::getHidden($contextKeys::authIdKey());

        if (! is_string($guard) || is_null($id)) {
            $next($job);

            return;
        }

        $replayGuard = $this->resolveReplayGuard($guard);

        if (! $replayGuard) {
            $next($job);

            return;
        }

        $previousGuard = Auth::getDefaultDriver();

        try {
            Auth::shouldUse($replayGuard);
            Auth::guard($replayGuard)->onceUsingId($id);

            $next($job);
        } finally {
            Auth::forgetGuards();
            Auth::shouldUse($previousGuard);
        }
    }

    private function resolveReplayGuard(string $guard): ?string
    {
        if (Auth::guard($guard) instanceof StatefulGuard) {
            return $guard;
        }

        $provider = config("auth.guards.$guard.provider");

        foreach (config('auth.guards') as $name => $config) {
            if (($config['provider'] ?? null) !== $provider) {
                continue;
            }

            if (Auth::guard($name) instanceof StatefulGuard) {
                return $name;
            }
        }

        return null;
    }
}

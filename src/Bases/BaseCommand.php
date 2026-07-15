<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Bases;

use Closure;
use Illuminate\Console\Command;
use Throwable;

abstract class BaseCommand extends Command
{
    protected function success(string $message): int
    {
        $this->components->info($message);

        return self::SUCCESS;
    }

    protected function failure(string $message, int $status = self::FAILURE): int
    {
        $this->components->error($message);

        return $status;
    }

    protected function optionAsInt(string $name, int $default = 0, ?int $minimum = null): int
    {
        $value = $this->option($name);

        if ($value === null || $value === false || $value === '') {
            return $default;
        }

        $value = (int) $value;

        return $minimum === null ? $value : max($minimum, $value);
    }

    protected function runSafely(Closure $callback): int
    {
        try {
            $result = $callback();

            return is_int($result) ? $result : self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);

            return $this->failure($exception->getMessage());
        }
    }
}

<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Bases;

use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\Attributes\Tries;

#[Tries(3)]
#[Queue('events')]
abstract class Subscriber implements ShouldQueueAfterCommit
{
    /**
     * @return array<class-string, string>
     */
    abstract public function subscribe(Dispatcher $events): array;

    /**
     * @return array<int, object>
     */
    public function middleware(): array
    {
        return [];
    }
}

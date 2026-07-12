<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Bases;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class Event
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @return array<int, mixed>
     */
    public function broadcastOn(): array
    {
        return [];
    }
}

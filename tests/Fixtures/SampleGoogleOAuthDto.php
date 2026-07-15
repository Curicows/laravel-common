<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Fixtures;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class SampleGoogleOAuthDto extends Data
{
    public function __construct(
        public string $refreshToken,
        public Carbon $createdAt,
    ) {}
}

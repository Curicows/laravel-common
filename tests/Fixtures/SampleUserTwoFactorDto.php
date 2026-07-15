<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Fixtures;

use Spatie\LaravelData\Data;

class SampleUserTwoFactorDto extends Data
{
    /**
     * @param  array<int, string>  $methods
     */
    public function __construct(
        public array $methods = [],
    ) {}

    public static function blank(): self
    {
        return new self;
    }
}

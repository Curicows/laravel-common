<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Bases;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\PaginatedDataCollection;

abstract class Dto extends Data
{
    public static function data(mixed $items): DataCollection
    {
        return static::collect($items, DataCollection::class);
    }

    public static function paginate(mixed $items): PaginatedDataCollection
    {
        return static::collect($items, PaginatedDataCollection::class);
    }
}

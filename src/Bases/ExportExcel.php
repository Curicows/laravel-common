<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Bases;

use Maatwebsite\Excel\Concerns\Exportable;

abstract class ExportExcel
{
    use Exportable;
}

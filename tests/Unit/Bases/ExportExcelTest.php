<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Bases;

use Curicows\LaravelCommon\Bases\ExportExcel;
use Maatwebsite\Excel\Concerns\Exportable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ExportExcel::class)]
class ExportExcelTest extends TestCase
{
    public function test_export_excel_uses_exportable_concern(): void
    {
        $usedTraits = class_uses_recursive(SampleExportExcel::class);

        self::assertContains(Exportable::class, $usedTraits);
    }
}

class SampleExportExcel extends ExportExcel {}

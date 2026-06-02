<?php

declare(strict_types=1);

namespace App\Modules\Analytics\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Generic Excel export for a built report (headers + rows).
 */
class ArrayReportExport implements FromArray, WithHeadings
{
    /**
     * @param  array<int, array<int, string>>  $rows
     * @param  array<int, string>  $headers
     */
    public function __construct(protected array $rows, protected array $headers) {}

    /** @return array<int, array<int, string>> */
    public function array(): array
    {
        return $this->rows;
    }

    /** @return array<int, string> */
    public function headings(): array
    {
        return $this->headers;
    }
}

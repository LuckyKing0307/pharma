<?php

namespace App\Exports;

use App\Exports\Sheets\Region;
use App\Exports\Sheets\Tablet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class TabletsExport implements WithMultipleSheets, ShouldQueue, ShouldAutoSize
{
    use Exportable;

    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new Tablet();

        $sheets[] = new Region();

        return $sheets;
    }

}

<?php

namespace App\Exports;

use App\Exports\Sheets\Others;
use App\Exports\Sheets\Region;
use App\Exports\Sheets\Tablet;
use App\Models\RegionMatrix;
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

        $tablets = new Tablet();
        $sheets[] = $tablets;
        $regions = RegionMatrix::all();
        foreach ($regions as $region){
            $sheets[] = new Region($region);
        }
//        $sheets[] = new Others($sheets);

        return $sheets;
    }

}

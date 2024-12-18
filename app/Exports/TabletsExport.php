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
    public $filter;
    public function __construct($data)
    {
        $this->filter = $data;
    }
    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];

        $tablets = new Tablet($this->filter);
        $sheets[] = $tablets;
        if (in_array('all', $this->filter['region'])) {
            $regions = RegionMatrix::all();
        }else{
            $regions = RegionMatrix::whereIn('id',$this->filter['region'])->get();
        }
        foreach ($regions as $region){
            $sheets[] = new Region($region,$this->filter);
        }
        $sheets[] = new Others($sheets,$this->filter);

        return $sheets;
    }

}

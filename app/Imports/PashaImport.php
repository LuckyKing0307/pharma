<?php

namespace App\Imports;

use App\Models\PashaData;
use App\Models\TabletMatrix;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;

class PashaImport implements ToModel, WithStartRow
{

    public string $firm = 'pasha-k';
    public string $file_id;
    public int $tabletNameRow = 0;

    public function __construct($file_id)
    {
        $this->file_id= $file_id;
    }
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {
        $region_name=str_replace('PASHA-K ','',$row[1]);
        $region_name=str_replace(' ','',$region_name);
        PashaData::create([
            'aptek_name' => $row[1],
            'tablet_name' => $row[0],
            'qty' => isset($row[4]) ? $row[4] : '',
            'region_name' => $row[2],
            'main_parent' => $region_name,
            'sales_qty' => $row[3],
            'uploaded_file_id' => $this->file_id,
            'uploaded_date' => Carbon::now(),
        ]);

        $tablets = TabletMatrix::where(['pasha-k' => $row[$this->tabletNameRow]]);
        if (!$tablets->exists()){
            TabletMatrix::create([
                $this->firm => $row[$this->tabletNameRow],
            ]);
        } else{
            $tablets->get();
            foreach ($tablets as $tablet){
                $tablet->update([$this->firm => $row[$this->tabletNameRow]]);
            }
        }
    }

    public function startRow(): int
    {
        return 2;
    }
}

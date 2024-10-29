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

class PashaImport implements ToModel, WithStartRow, WithChunkReading, WithBatchInserts, ShouldQueue
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
        $region_name = '';
        if ($region_name==''){
            if (strpos($row[1],'PASHA-K')!==false){
                $region_name=str_replace('PASHA-K ','',$row[1]);
            }else{
                $region_name = $row[2];
            }
        }
        PashaData::create([
            'aptek_name' => $row[1],
            'tablet_name' => $row[0],
            'qty' => $row[4] ? $row[4] : '',
            'region_name' => $region_name,
            'sales_qty' => $row[3],
            'ost_qty' => $row[5] ? $row[5] : '',
            'uploaded_file_id' => $this->file_id,
            'uploaded_date' => Carbon::now(),
        ]);

        $tablets = TabletMatrix::where(['avromed' => $row[$this->tabletNameRow]])
            ->orWhere(['azerimed' => $row[$this->tabletNameRow]])
            ->orWhere(['aztt' => $row[$this->tabletNameRow]])
            ->orWhere(['epidbiomed' => $row[$this->tabletNameRow]])
            ->orWhere(['pasha-k' => $row[$this->tabletNameRow]])
            ->orWhere(['radez' => $row[$this->tabletNameRow]])
            ->orWhere(['sonar' => $row[$this->tabletNameRow]])
            ->orWhere(['zeytun' => $row[$this->tabletNameRow]]);
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

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}

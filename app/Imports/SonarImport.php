<?php

namespace App\Imports;

use App\Models\SonarData;
use App\Models\TabletMatrix;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;

class SonarImport implements ToModel, WithStartRow, WithChunkReading, WithBatchInserts, ShouldQueue
{

    public string $firm = 'sonar';
    public string $file_id;
    public int $tabletNameRow = 0;

    public function __construct($file_id)
    {
        $this->file_id= $file_id;
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        SonarData::create([
            'aptek_name' => $row[2],
            'tablet_name' => $row[0],
            'region_name' => $row[1],
            'region' => $row[1],
            'sales_qty' => $row[4],
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

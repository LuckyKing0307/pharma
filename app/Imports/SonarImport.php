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

class SonarImport implements ToModel, WithStartRow, WithChunkReading, WithBatchInserts
{
    public string $firm = 'sonar';
    public string $file_id;
    public int $tabletNameRow = 0;

    public function __construct($file_id)
    {
        $this->file_id = $file_id;
    }
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $tablet_name = str_replace('  ', ' ', $row[0]);
        SonarData::create([
            'aptek_name' => $row[2],
            'tablet_name' => $tablet_name,
            'region_name' => $row[1],
            'region' => $row[1],
            'sales_qty' => $row[4],
            'uploaded_file_id' => $this->file_id,
            'uploaded_date' => Carbon::now(),
        ]);

        $tablets = TabletMatrix::where(['sonar' => $tablet_name]);
        if (!$tablets->exists()){
            TabletMatrix::create([
                $this->firm => $tablet_name,
            ]);
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

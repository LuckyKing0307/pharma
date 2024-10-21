<?php

namespace App\Imports;

use App\Models\TabletMatrix;
use App\Models\ZeytunData;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;

class ZeytunImport implements ToModel, WithStartRow, WithChunkReading, WithBatchInserts
{

    public string $firm = 'zeytun';
    public string $file_id;
    public int $tabletNameRow = 1;

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
        info($row[1]);
        if (str_contains($row['1'], ' шт') or str_contains($row['1'], ' упак') or str_contains($row['1'], ' флак') or str_contains($row['1'], ' тюб')) {
            ZeytunData::create([
                'tablet_name' => $row[1],
                'sales_qty' => $row[2],
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
            } elseif ($row[$this->tabletNameRow]!='Итог'){
                $tablets->get();
                foreach ($tablets as $tablet){
                    $tablet->update([$this->firm => $row[$this->tabletNameRow]]);
                }
            }
        }else{
            if ($row[1]!='Итог' and $row[1]!=''){
                $tablet = ZeytunData::where(['aptek_name' => null])->orderBy('created_at', 'desc');
                if ($tablet->exists()){
                    $tablet = $tablet->first();
                    ZeytunData::create([
                        'aptek_name' => $row[1],
                        'tablet_name' => $tablet->tablet_name,
                        'sales_qty' => $row[2],
                        'uploaded_date' => Carbon::now(),
                        'uploaded_file_id' => $this->file_id,
                    ]);
                }
            }
        }
    }

    public function startRow(): int
    {
        return 12;
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

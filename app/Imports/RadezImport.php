<?php

namespace App\Imports;

use App\Models\RadezData;
use App\Models\TabletMatrix;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithStartRow;

class RadezImport implements ToModel,WithStartRow, WithChunkReading, WithBatchInserts, ShouldQueue
{

    public string $firm = 'radez';
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
        if ($row[0]!='ЯЩМЯДЛИ ИЛЩАМ АСЛ' and $row[4]=='' and (!str_contains($row['0'], 'АПТЕК') and (str_contains($row['0'], ' АМЛО ') or str_contains($row['0'], 'мг ') or str_contains($row['0'], ' N') or str_contains($row['0'], ' крем') or str_contains($row['0'], ' свеч') or str_contains($row['0'], 'мл ') or str_contains($row['0'], 'гр ') or str_contains($row['0'], ' №')))){
            RadezData::create([
                'tablet_name' => $row[0],
                'sales_qty' => $row[1],
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
            } elseif ($row[$this->tabletNameRow]!='Name'){
                $tablets->get();
                foreach ($tablets as $tablet){
                    $tablet->update([$this->firm => $row[$this->tabletNameRow]]);
                }
            }
        }else{
            if ($row[0]!=''){
                $tablet = RadezData::where(['aptek_name' => null])->orderBy('created_at', 'desc')->first();
                RadezData::create([
                    'aptek_name' => $row[0],
                    'tablet_name' => $tablet->tablet_name,
                    'sales_qty' => $row[1],
                    'uploaded_date' => Carbon::now(),
                    'uploaded_file_id' => $this->file_id,
                ]);
            }
            if ($row[3]!=''){
                $tablet = RadezData::where(['aptek_name' => null])->orderBy('created_at', 'desc')->first();
                RadezData::create([
                    'aptek_name' => $row[3],
                    'tablet_name' => $tablet->tablet_name,
                    'sales_qty' => $row[4],
                    'uploaded_date' => Carbon::now(),
                    'uploaded_file_id' => $this->file_id,
                ]);
            }
        }
    }

    public function startRow(): int
    {
        return 4;
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

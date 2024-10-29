<?php

namespace App\Imports;

use App\Models\AzerimedData;
use App\Models\AzttData;
use App\Models\TabletMatrix;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class AzttImport implements ToModel, WithChunkReading, WithBatchInserts, ShouldQueue
{
    use RemembersRowNumber;

    public string $firm = 'aztt';
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
        if (strpos($row[0], '№')){
            AzttData::create([
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
                $tablet = AzttData::where(['aptek_name' => null])->orderBy('created_at', 'desc')->first();
                AzttData::create([
                    'aptek_name' => $row[0],
                    'tablet_name' => $tablet->tablet_name,
                    'sales_qty' => $row[1],
                    'uploaded_date' => Carbon::now(),
                    'uploaded_file_id' => $this->file_id,
                    'region_name' => '',
                ]);
            }
        }
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

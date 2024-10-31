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

class RadezImport implements ToModel,WithStartRow, WithChunkReading, WithBatchInserts
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
        if ($row[0]!='ЯЩМЯДЛИ ИЛЩАМ АСЛ' and $row[4]=='' and (!str_contains($row['0'], 'АПТЕК') and (str_contains($row['0'], ' АМЛО ') or str_contains($row['0'], 'mq ') or str_contains($row['0'], ' sprey') or str_contains($row['0'], ' krem') or str_contains($row['0'], ' N') or str_contains($row['0'], ' tabl')  or str_contains($row['0'], 'ml ') or str_contains($row['0'], 'mg ') or str_contains($row['0'], 'mg ') or str_contains($row['0'], 'maz ') or str_contains($row['0'], 'krem ') or str_contains($row['0'], ' №')))){
            RadezData::create([
                'tablet_name' => $row[0],
                'sales_qty' => $row[1],
                'uploaded_date' => Carbon::now(),
                'uploaded_file_id' => $this->file_id,
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
            $region_name = '';
            if ($row[0]!=''){
                $region_names = explode(' ', $row[0]);
                if (count($region_names)>1){
                    if (isset($region_names[1]) and $region_names[1]=='APTEK'){
                        $region_name = isset($region_names[1])?$region_names[1]:'';
                    }else{
                        $region_name = isset($region_names[2])?$region_names[2]:'';
                    }
                }
                $tablet = RadezData::where(['aptek_name' => null])->orderBy('created_at', 'desc')->first();

                RadezData::create([
                    'aptek_name' => $row[0],
                    'tablet_name' => $tablet->tablet_name,
                    'sales_qty' => $row[1],
                    'uploaded_date' => Carbon::now(),
                    'uploaded_file_id' => $this->file_id,
                    'region_name' => $region_name,
                ]);
            }
            if ($row[3]!=''){
                if (count(explode(' ', $row[3]))>1){
                    $region_names = explode(' ', $row[3]);
                    if (isset($region_names[1]) and $region_names[1]=='APTEK'){
                        $region_name = isset($region_names[1])?$region_names[1]:'';
                    }else{
                        $region_name = isset($region_names[2])?$region_names[2]:'';
                    }
                }
                $tablet = RadezData::where(['aptek_name' => null])->orderBy('created_at', 'desc')->first();
                RadezData::create([
                    'aptek_name' => $row[3],
                    'tablet_name' => $tablet->tablet_name,
                    'sales_qty' => $row[4],
                    'uploaded_date' => Carbon::now(),
                    'uploaded_file_id' => $this->file_id,
                    'region_name' => $region_name,
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

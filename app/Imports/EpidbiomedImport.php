<?php

namespace App\Imports;

use App\Models\EpidbiomedData;
use App\Models\TabletMatrix;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class EpidbiomedImport implements ToModel, WithStartRow
{

    public string $firm = 'epidbiomed';
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
        if (strtolower($row[0])!='Итого'){
                EpidbiomedData::create([
                    'tablet_name' => $row[0],
                    'qty' => isset($row[6]) ? $row[6] : '',
                    'sales_qty' => $row[5],
                    'ost_qty' => isset($row[7]) ? $row[7] : '',
                    'uploaded_file_id' => $this->file_id,
                    'uploaded_date' => Carbon::now(),
                ]);
            if (strpos(strtolower($row[0]),'mg')){
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
            }
        }
    }

    public function startRow(): int
    {
        return 13;
    }

}

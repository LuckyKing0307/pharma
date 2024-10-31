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
                    'region_name' => '',
                ]);
            if (strpos(strtolower($row[0]),'mg')){
                $tablets = TabletMatrix::where(['epidbiomed' => $row[$this->tabletNameRow]]);
                if (!$tablets->exists()){
                    TabletMatrix::create([
                        $this->firm => $row[$this->tabletNameRow],
                    ]);
                }
            }
        }
    }

    public function startRow(): int
    {
        return 13;
    }

}

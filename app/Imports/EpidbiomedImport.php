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
            if ($row[0]!='ЯЩМЯДЛИ ИЛЩАМ АСЛ' and (!str_contains($row['0'], 'АПТЕК') and (str_contains($row['0'], ' АМЛО ') or str_contains($row['0'], 'MQ ') or str_contains($row['0'], 'MG ') or str_contains($row['0'], ' RASTVOR') or str_contains($row['0'], ' krem') or str_contains($row['0'], ' N') or str_contains($row['0'], ' tabl')  or str_contains($row['0'], 'ml ') or str_contains($row['0'], 'mg ') or str_contains($row['0'], 'maz ') or str_contains($row['0'], 'krem ') or str_contains($row['0'], ' SPREY ') or str_contains($row['0'], ' EGIS') or str_contains($row['0'], ' aerosol ')  or str_contains($row['0'], ' №')))) {
                EpidbiomedData::create([
                    'tablet_name' => $row[0],
                    'qty' => isset($row[6]) ? $row[6] : '',
                    'sales_qty' => $row[5],
                    'ost_qty' => isset($row[7]) ? $row[7] : '',
                    'uploaded_file_id' => $this->file_id,
                    'uploaded_date' => Carbon::now(),
                    'region_name' => null,
                ]);
            }else{
                $region_names = explode(' ', $row[0]);
                if (count($region_names)>1){
                    if (isset($region_names[1]) and $region_names[1]=='APTEK'){
                        $region_name = isset($region_names[1])?$region_names[1]:'';
                    }else{
                        $region_name = isset($region_names[2])?$region_names[2]:'';
                    }
                }
                $tablet = EpidbiomedData::where(['region_name' => null])->orderBy('id', 'desc')->first();
                EpidbiomedData::create([
                    'tablet_name' => $tablet->tablet_name,
                    'qty' => isset($row[6]) ? $row[6] : '',
                    'sales_qty' => $row[5],
                    'ost_qty' => isset($row[7]) ? $row[7] : '',
                    'uploaded_file_id' => $this->file_id,
                    'uploaded_date' => Carbon::now(),
                    'region_name' => $row[0],
                ]);
            }
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

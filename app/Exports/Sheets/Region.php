<?php

namespace App\Exports\Sheets;

use App\Models\AvromedData;
use App\Models\AzerimedData;
use App\Models\MainTabletMatrix;
use App\Models\PashaData;
use App\Models\RegionMatrix;
use App\Models\SonarData;
use App\Models\UploadedFile;
use App\Models\ZeytunData;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Region implements FromCollection, ShouldQueue, ShouldAutoSize, WithStyles, WithTitle
{
    use Exportable;
    public $regions_array = [0=>["a"=>'', 'tablet_name'=>'Лекарства'],1=>["a"=>'', 'tablet_name'=>'']];

    /**
     * @return array
     */
    public function collection(): Collection
    {
        $tablets = MainTabletMatrix::all();
        $regions = RegionMatrix::all();
        foreach ($regions as $region) {
            $count = count($this->regions_array[0]);
            $this->regions_array[0][$count] = $region->mainname;
            $tablet_data = [];
            foreach ($tablets as $tablet) {
                $month = AvromedData::where([['tablet_name', '=', $tablet->avromed],['aptek_name', '!=', '']])->orderBy('created_at', 'desc')->first();
                if ($month){
                    $this->tablets[0]['month_sales'] = 'Продажи за '.$month->created_at->format('F');
                    $this->tablets[0]['month'] = $month->created_at->format('F');
                }
                $tablet_data['a'] = '';
                $tablet_data['tablet_name'] = $tablet->mainname;
                $tablet_data[$count] = 0;
                $pasha_data = 'pasha-k';
                $avromed_datas = AvromedData::where([['tablet_name', '=', $tablet->avromed],['aptek_name', '!=', ''], ['region_name','=',$region->avromed]])->get();
                $azerimed_datas = AzerimedData::where([['tablet_name', '=', $tablet->azerimed],['aptek_name', '!=', ''], ['region_name','=',$region->azerimed]])->get();
                $sonar_datas = SonarData::where([['tablet_name', '=', $tablet->sonar],['aptek_name', '!=', ''], ['region_name','=',$region->sonar]])->get();
                $pasha_datas = PashaData::where([['tablet_name', '=', $tablet->sonar],['aptek_name', '!=', ''], ['region_name','=',$region->$pasha_data]])->get();

                foreach ($avromed_datas as $tableted) {
                    $file = UploadedFile::find($tableted->uploaded_file_id);
                    if ($file){
                        $tablet_data[$count] +=$tableted->sales_qty;
                    }
                }foreach ($azerimed_datas as $tableted) {
                    $file = UploadedFile::find($tableted->uploaded_file_id);
                    if ($file){
                        $tablet_data[$count] +=$tableted->sales_qty;
                    }
                }foreach ($sonar_datas as $tableted) {
                    $file = UploadedFile::find($tableted->uploaded_file_id);
                    if ($file){
                        $tablet_data[$count] +=$tableted->sales_qty;
                    }
                }foreach ($pasha_datas as $tableted) {
                    $file = UploadedFile::find($tableted->uploaded_file_id);
                    if ($file){
                        $tablet_data[$count] +=$tableted->sales_qty;
                    }
                }
                if (isset($this->regions_array[$tablet->mainname])){
                    $this->regions_array[$tablet->mainname][$count] = $tablet_data[$count];
                }else{
                    $this->regions_array[$tablet->mainname] = $tablet_data;
                }

            }
        }

        return collect($this->regions_array);
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Regions';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('B1:W2')->getFill()->applyFromArray(['fillType' => 'solid','rotation' => 0, 'color' => ['rgb' => '324ea8'],]);
        $sheet->getStyle('B1:W2')->getFont()->applyFromArray([
            'name'      =>  'Calibri',
            'size'      =>  15,
            'bold'      =>  true,
            'color' => ['argb' => 'FFFFFF'],]);
        $sheet->getStyle('B1:W100')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ]
        ]);
    }
}

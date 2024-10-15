<?php

namespace App\Exports\Sheets;

use App\Models\AvromedData;
use App\Models\AzerimedData;
use App\Models\AzttData;
use App\Models\EpidbiomedData;
use App\Models\MainTabletMatrix;
use App\Models\PashaData;
use App\Models\RadezData;
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

class Tablet implements FromCollection, ShouldQueue, ShouldAutoSize, WithStyles, WithTitle
{
    use Exportable;
    public $regions_array = [0=>["a"=>'', 'name'=>'Лекарства']];
    public $tablets = [0 => [
        'a' =>'',
        'tablet_name' => 'Название препората',
        'price' => 'Цена',
        1 => 'Январь',
        2 => 'Февраль',
        3 => 'Март',
        4 => 'Апрель',
        5 => 'Май',
        6 => 'Июнь',
        7 => 'Июль',
        8 => 'Август',
        9 => 'Сентябрь',
        10 => 'Октябрь',
        11 => 'Ноябрь',
        12 => 'Декабрь',
        13 => 'Продажи в $',
        21 => 'Январь',
        22 => 'Февраль',
        23 => 'Март',
        24 => 'Апрель',
        25 => 'Май',
        26 => 'Июнь',
        27 => 'Июль',
        28 => 'Август',
        29 => 'Сентябрь',
        30 => 'Октябрь',
        31 => 'Ноябрь',
        32 => 'Декабрь',
        'all_sales' => 'Общие продажи',
        'all_sales_price' => 'Общие продажи цена',
    ],1 => [
        'a' =>'',
        'tablet_name' => '',
        1 => '',
        2 => '',
        3 => '',
        4 => '',
        5 => '',
        6 => '',
        7 => '',
        8 => '',
        9 => '',
        10 => '',
        11 => '',
        12 => '',
        13 => '',
        21 => '',
        22 => '',
        23 => '',
        24 => '',
        25 => '',
        26 => '',
        27 => '',
        28 => '',
        29 => '',
        30 => '',
        31 => '',
        32 => '',
        'all_sales' => '',
        'all_sales_price' => '',
    ]];

    /**
     * @return array
     */
    public function collection(): Collection
    {
        $tablets = MainTabletMatrix::all();
        foreach ($tablets as $tablet) {
            $tablet_data = [];
            $avromed = AvromedData::where([['tablet_name', '=', $tablet->avromed],['aptek_name', '!=', '']]);
            $azzt = AzttData::where([['tablet_name', '=', $tablet->aztt],['aptek_name', '!=', '']]);
            $epidbiomed = EpidbiomedData::where([['tablet_name', '=', $tablet->epidomed],['aptek_name', '!=', '']]);
            $azerimed = AzerimedData::where([['tablet_name', '=', $tablet->azerimed],['aptek_name', '!=', '']]);
            $pasha = PashaData::where([['tablet_name', '=', $tablet->pasha],['aptek_name', '!=', '']]);
            $radez = RadezData::where([['tablet_name', '=', $tablet->radez],['aptek_name', '!=', '']]);
            $sonar = SonarData::where([['tablet_name', '=', $tablet->sonar],['aptek_name', '!=', '']]);
            $zeytun = ZeytunData::where([['tablet_name', '=', $tablet->zeytun],['aptek_name', '!=', '']]);
            $tablet_data['a'] = '';
            $tablet_data['tablet_name'] = $tablet->mainname;
            $tablet_data['price'] = $tablet->price;
            $tablet_data = $this->getFile($tablet_data, $avromed);
            $tablet_data = $this->getFile($tablet_data, $azzt);
            $tablet_data = $this->getFile($tablet_data, $epidbiomed);
            $tablet_data = $this->getFile($tablet_data, $azerimed);
            $tablet_data = $this->getFile($tablet_data, $pasha);
            $tablet_data = $this->getFile($tablet_data, $radez);
            $tablet_data = $this->getFile($tablet_data, $sonar);
            $tablet_data = $this->getFile($tablet_data, $zeytun);
            $tablet_data['all_sales'] = 0;
            for ($i = 1; $i <= 12; $i++) {
                if (isset($tablet_data[$i])){
                    $tablet_data['all_sales'] =  $tablet_data['all_sales']+$tablet_data[$i];
                }
            }
            $tablet_data['all_sales_price'] = $tablet->price*$tablet_data['all_sales'].'$';
            $this->tablets[] = $tablet_data;
        }
        return collect($this->tablets);
    }

    public function getFile($data, $tablets)
    {
        for ($i = 1; $i <= 12; $i++) {
            if (!isset($data[$i])){
                $data[$i] = 0;
            }
        }
        $data[13] = '';
        for ($i = 21; $i <= 32; $i++) {
            if (!isset($data[$i])){
                $data[$i] = 0;
            }
        }
        foreach ($tablets->get() as $tablet) {
            $file = UploadedFile::find($tablet->uploaded_file_id);
            if ($file!=null){
                info($tablet->uploaded_file_id);
                if ($file->uploaded_date){
                    $data[Carbon::make($file->uploaded_date)->month] += $tablet->sales_qty;
                    $data[Carbon::make($file->uploaded_date)->month+20] += $tablet->sales_qty*$data['price'];
                }else{
                    $data[Carbon::now()->month] += $tablet->sales_qty;
                    $data[Carbon::now()->month+20] += $tablet->sales_qty*$data['price'];
                }
            }
        }
        return $data;
    }
    /**
     * @return string
     */
    public function title(): string
    {
        return 'Supplies';
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('B1:AD2')->getFill()->applyFromArray(['fillType' => 'solid','rotation' => 0, 'color' => ['rgb' => '324ea8'],]);
        $sheet->getStyle('B1:AD2')->getFont()->applyFromArray([
            'name'      =>  'Calibri',
            'size'      =>  15,
            'bold'      =>  true,
            'color' => ['argb' => 'FFFFFF'],]);
        $sheet->getStyle('B1:AD100')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ]
        ]);
    }
}

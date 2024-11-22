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
        'tablet_name' => 'SKU',
        'price' => 'Net prices',
        1 => 'Jan',
        2 => 'Feb',
        3 => 'Mar',
        4 => 'Apr',
        5 => 'May',
        6 => 'Jun',
        7 => 'Jul',
        8 => 'Avg',
        9 => 'Sep',
        10 => 'Oct',
        11 => 'Nov',
        12 => 'Dec',
        13 => 'Sales according price',
        21 => 'Jan',
        22 => 'Feb',
        23 => 'Mar',
        24 => 'Apr',
        25 => 'May',
        26 => 'Jun',
        27 => 'Jul',
        28 => 'Avg',
        29 => 'Sep',
        30 =>  'Oct',
        31 =>  'Nov',
        32 =>  'Dec',
        'all_sales' => 'Total qty.',
        'all_sales_price' => 'Closing stock',
    ],1 => [
        'a' =>'',
        'tablet_name' => '',
        'price' => '',
        1 => 0,
        2 => 0,
        3 => 0,
        4 => 0,
        5 => 0,
        6 => 0,
        7 => 0,
        8 => 0,
        9 => 0,
        10 => 0,
        11 => 0,
        12 => 0,
        13 => 0,
        21 => 0,
        22 => 0,
        23 => 0,
        24 => 0,
        25 => 0,
        26 => 0,
        27 => 0,
        28 => 0,
        29 => 0,
        30 => 0,
        31 => 0,
        32 => 0,
        'all_sales' => 0,
        'all_sales_price' => 0,
    ]];

    /**
     * @return array
     */
    public function collection(): Collection
    {
        $tablets = MainTabletMatrix::all();
        foreach ($tablets as $tablet) {
            $tablet_data = [];
            $pasha_data = 'pasha-k';
            $avromed = AvromedData::where([['tablet_name', '=', $tablet->avromed]]);
            $azzt = AzttData::where([['tablet_name', '=', $tablet->aztt],['aptek_name', '!=', '']]);
            $epidbiomed = EpidbiomedData::where([['tablet_name', '=', $tablet->epidbiomed]])->where('region_name', null);
            $azerimed = AzerimedData::where([['tablet_name', '=', $tablet->azerimed]]);
            $pasha = PashaData::where([['tablet_name', '=', $tablet->$pasha_data]]);
            $radez = RadezData::where([['tablet_name', '=', $tablet->radez]])->where('aptek_name', null);
            $sonar = SonarData::where([['tablet_name', '=', $tablet->sonar]]);
            $zeytun = ZeytunData::where([['tablet_name', '=', $tablet->zeytun]])->where('aptek_name', null);
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
            $price = str_replace(',', '.', $tablet_data['price']);
            if ($tablet_data['all_sales']>90000){
                $tablet_data['all_sales'] = 0;
            }
            $tablet_data['all_sales_price'] = $price*floatval($tablet_data['all_sales']);
            $this->tablets[1]['all_sales'] = $this->tablets[1]['all_sales']+$tablet_data['all_sales'];
            $this->tablets[1]['all_sales_price'] = $this->tablets[1]['all_sales_price']+($price*floatval($tablet_data['all_sales']));
            $this->tablets[] = $tablet_data;
            for ($i = 1; $i<=12; $i++){
                $this->tablets[1][$i] += $tablet_data[$i];
                if ($this->tablets[1][$i]!='' and $this->tablets[1][$i]>0){
                    $this->tablets[1][$i+20] += $tablet_data[$i+20];
                }
            }
        }
        return collect($this->tablets);
    }

    public function getFile($data, $tablets)
    {
        for ($i = 1; $i <= 13; $i++) {
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
        $price = str_replace(',', '.', $data['price']);
        foreach ($tablets->get() as $tablet) {
            $file = UploadedFile::where(['file_id' => $tablet->uploaded_file_id]);
            if ($file->exists()){
                $file = $file->get()->first();
                if ($file->uploaded_date){
                    $data[Carbon::make($file->uploaded_date)->month] += $tablet->sales_qty;
                    $data[Carbon::make($file->uploaded_date)->month+20] += floatval($tablet->sales_qty)*$price;
                    if ($data[Carbon::make($file->uploaded_date)->month]>90000){
                        $data[Carbon::make($file->uploaded_date)->month] = 0;
                        $data[Carbon::make($file->uploaded_date)->month+20] = 0;
                    }
                }else{
                    $data[Carbon::now()->month] += $tablet->sales_qty;
                    $data[Carbon::now()->month+20] += floatval($tablet->sales_qty)*$price;
                    if ($data[Carbon::now()->month]>90000){
                        $data[Carbon::now()->month] = 0;
                        $data[Carbon::now()->month+20] = 0;
                    }
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

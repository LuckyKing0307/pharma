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

class Region implements FromCollection, ShouldQueue, ShouldAutoSize, WithStyles, WithTitle
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
    public $region;

    public function __construct($region)
    {
        $this->region = $region;
    }
    /**
     * @return array
     */

    public function collection(): Collection
    {
        $tablets = MainTabletMatrix::all();
        $region = $this->region;
        foreach ($tablets as $tablet) {
            $tablet_data = [];
            $pasha_data = 'pasha-k';
            $avromed = AvromedData::where([['tablet_name', '=', $tablet->avromed], ['region_name','=',$region->avromed]]);
            $azerimed = AzerimedData::where([['tablet_name', '=', $tablet->azerimed], ['region_name','=',$region->azerimed]]);
            $pasha = PashaData::where([['tablet_name', '=', $tablet->pasha], ['region_name','=',$region->$pasha_data]]);
            $sonar = SonarData::where([['tablet_name', '=', $tablet->sonar],['aptek_name', '!=', ''], ['region_name','=',$region->sonar]]);
            $tablet_data['a'] = '';
            $tablet_data['tablet_name'] = $tablet->mainname;
            $tablet_data['price'] = $tablet->price;
            $tablet_data = $this->getFile($tablet_data, $avromed);
            $tablet_data = $this->getFile($tablet_data, $azerimed);
            $tablet_data = $this->getFile($tablet_data, $pasha);
            $tablet_data = $this->getFile($tablet_data, $sonar);
            $tablet_data['all_sales'] = 0;
            for ($i = 1; $i <= 12; $i++) {
                if (isset($tablet_data[$i])){
                    $tablet_data['all_sales'] =  $tablet_data['all_sales']+$tablet_data[$i];
                }
            }
            $price = str_replace(',', '.', $tablet_data['price']);
            $tablet_data['all_sales_price'] = $price*intval($tablet_data['all_sales']).' AZN';
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
            $file = UploadedFile::where(['file_id' => $tablet->uploaded_file_id]);
            if ($file->exists()){
                $file = $file->get()->first();
                if ($file->uploaded_date){
                    $data[Carbon::make($file->uploaded_date)->month] += $tablet->sales_qty;
                    $data[Carbon::make($file->uploaded_date)->month+20] += intval($tablet->sales_qty)*intval($data['price']);
                }else{
                    $data[Carbon::now()->month] += $tablet->sales_qty;
                    $data[Carbon::now()->month+20] += intval($tablet->sales_qty)*intval($data['price']);
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
        return $this->region->mainname;
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

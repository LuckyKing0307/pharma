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
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class Region implements FromCollection, ShouldQueue, ShouldAutoSize, WithTitle
{

    use Exportable;

    public $regions_array = [0 => ["a" => '', 'name' => 'Лекарства']];
    public $tablets = [0 => [
        'a' => '',
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
        30 => 'Oct',
        31 => 'Nov',
        32 => 'Dec',
        'all_sales' => 'Total qty.',
        'all_sales_price' => 'Closing stock',
    ], 1 => [
        'a' => '',
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

    public $depo_models = [
        'avromed' => \App\Models\AvromedData::class,
        'azerimed' => \App\Models\AzerimedData::class,
        'aztt' => \App\Models\AzttData::class,
        'epidbiomed' => \App\Models\EpidbiomedData::class,
        'pasha-k' => \App\Models\PashaData::class,
        'radez' => \App\Models\RadezData::class,
        'sonar' => \App\Models\SonarData::class,
        'zeytun' => \App\Models\ZeytunData::class,
    ];
    public $region;
    public $filter;

    public function __construct($region, $filter)
    {
        $this->region = $region;
        $this->filter = $filter;
    }

    /**
     * @return array
     */

    public function collection(): Collection
    {
        $tablets = MainTabletMatrix::all();
        $region = $this->region;
        $region_tablet_array = $this->getTabletsFromDepo($region);
        foreach ($tablets as $tablet) {
            $tablet_data = [
                'a' => '',
                'tablet_name' => $tablet->mainname,
                'price' => $tablet->price,
            ];
            $price = str_replace(',', '.', $tablet_data['price']);
            foreach ($region_tablet_array as $region_tablet => $region_tablet_data) {
                foreach ($region_tablet_data as $region_month => $region_month_data) {
                    $search = $tablet->$region_tablet;
                    $tab_array = json_decode($region_month_data, 1);
                    foreach ($tab_array as $tableted) {
                        if (str_replace(' ', '', $tableted['tablet_name']) == str_replace(' ', '', $search)) {
                            $tablet_data = $this->updateSalesData($tablet_data, $region_month, $tableted['total_sales'], floatval($price));
                        }
                    }
                }
            }
            $tablet_data['all_sales'] = 0;
            for ($i = 1; $i <= 12; $i++) {
                if (isset($tablet_data[$i])) {
                    $tablet_data['all_sales'] = $tablet_data['all_sales'] + $tablet_data[$i];
                }
            }
            $tablet_data['all_sales_price'] = floatval($price) * floatval($tablet_data['all_sales']);
            $this->tablets[1]['all_sales'] = $this->tablets[1]['all_sales'] + $tablet_data['all_sales'];
            $this->tablets[1]['all_sales_price'] = $this->tablets[1]['all_sales_price'] + (floatval($price) * floatval($tablet_data['all_sales']));
            $this->tablets[] = $tablet_data;
            for ($i = 1; $i <= 12; $i++) {
                if (isset($tablet_data[$i]) and isset($this->tablets[1][$i])) {
                    $this->tablets[1][$i] += $tablet_data[$i];
                    $this->tablets[1][$i + 20] += $tablet_data[$i + 20];
                }
            }
        }
        return collect($this->tablets);
    }

    /**
     * Обновляет данные о продажах.
     */
    private function updateSalesData($data, $month, $salesQty, $price)
    {
        foreach (range(1, 13) as $i) {
            $data[$i] = $data[$i] ?? 0;
        }
        foreach (range(21, 32) as $i) {
            $data[$i] = $data[$i] ?? 0;
        }
        $data[13] = '';
        $data[$month] += floatval($salesQty);
        $data[$month + 20] += floatval($salesQty) * $price;

        // Лимит продаж
        if ($data[$month] > 80000) {
            $data[$month] = 0;
            $data[$month + 20] = 0;
        }
        return $data;
    }

    public function getTabletsFromDepo($region)
    {
        $depo_data = [];
        $depos = in_array('all', $this->filter['depo'])
            ? array_keys($this->depo_models)
            : $this->filter['depo'];
        foreach ($depos as $depo) {
            if ($region->$depo==''){
                continue;
            }
            $model = $this->depo_models[$depo];
            $depo_file = str_replace('-k','',$depo);
            $fileQuery = UploadedFile::where('which_depo', $depo_file);
            if (!empty($this->filter['from'])) {
                $fileQuery->where('uploaded_date', '>=', $this->filter['from']);
            }
            if (!empty($this->filter['to'])) {
                $fileQuery->where('uploaded_date', '<=', $this->filter['to']);
            }
            if ($fileQuery->exists()) {
                $files = $fileQuery->get();
                foreach ($files as $file) {
                    $month = $file->uploaded_date
                        ? Carbon::make($file->uploaded_date)->month
                        : Carbon::now()->month;
                    $depo_resalts = $model::query()->select('tablet_name', DB::raw('SUM(sales_qty) AS total_sales'))
                        ->where('uploaded_file_id', $file->file_id);
                    if ($depo!='avromed' or $depo=='pasha-k') {
                        if (is_array(json_decode($region->$depo, 1))) {
                            $depo_resalts->where('aptek_name', '%'.json_decode($region->$depo, 1)[0].'%');
                            foreach (json_decode($region->$depo, 1) as $radez_aptek) {
                                if(json_decode($region->$depo, 1)[0]!=$radez_aptek){
                                    $depo_resalts->orWhere([['aptek_name','like', '%'.$radez_aptek.'%'],['uploaded_file_id','=', $file->file_id]]);
                                }
                            }
                        }else{
                            $depo_resalts->where('region_name', $region->$depo);
                        }
                    }else{
                        $reg_depo = $region->$depo;
                        if ($depo == 'pasha-k'){
                            $reg = $region->pasha_extra;
                        }else{
                            $reg = $region->avromed_extra;
                        }
                        if($reg){
                            info('asdsdasd');
                            info($reg);
                            $depo_resalts->where(function ($query) use ($reg_depo,$depo,$reg) {
                                info($query->where('region_name','like', '%'.$reg_depo.'%')
                                    ->orWhere('main_parent','like', '%'.$reg.'%')->toSql());
                                return $query->where('region_name','like', '%'.$reg_depo.'%')
                                    ->orWhere('main_parent','like', '%'.$reg.'%');
                            });
                        }else{
                            $depo_resalts->where('region_name','like', '%'.$reg_depo.'%');
                        }
                    }
                    $depo_data[$depo][$month] = $depo_resalts->groupBy('tablet_name')
                        ->get();

                }
            }
        }
        return $depo_data;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->region->mainname;
    }

//    public function styles(Worksheet $sheet)
//    {
//        $sheet->getStyle('B1:AD2')->getFill()->applyFromArray(['fillType' => 'solid','rotation' => 0, 'color' => ['rgb' => '324ea8'],]);
//        $sheet->getStyle('B1:AD2')->getFont()->applyFromArray([
//            'name'      =>  'Calibri',
//            'size'      =>  15,
//            'bold'      =>  true,
//            'color' => ['argb' => 'FFFFFF'],]);
//        $sheet->getStyle('B1:AD100')->applyFromArray([
//            'borders' => [
//                'allBorders' => [
//                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
//                    'color' => ['argb' => '000000'],
//                ],
//            ]
//        ]);
//    }
}

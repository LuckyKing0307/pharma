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

class Tablet implements FromCollection, ShouldQueue, ShouldAutoSize, WithTitle
{
    use Exportable;

    public $regions_array = [0 => ["a" => '', 'name' => 'Лекарства']];
    public $filter;
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

    public function __construct($data)
    {
        $this->filter = $data;
    }

    /**
     * @return array
     */
    public function collection(): Collection
    {
        $tablets = MainTabletMatrix::all();

        foreach ($tablets as $tablet) {
            $tablet_data = [
                'a' => '',
                'tablet_name' => $tablet->mainname,
                'price' => $tablet->price,
            ];

            $results = $this->processTablet($tablet);

            foreach ($results as $result) {
                $tablet_data = $this->getFile($tablet_data, $result);
            }
            $tablet_data['all_sales'] = 0;
            // Подсчет общего объема продаж
            for ($i = 1; $i <= 12; $i++) {
                if (isset($tablet_data[$i])) {
                    $tablet_data['all_sales'] += $tablet_data[$i];
                }
            }

            // Учет лимита продаж
            if ($tablet_data['all_sales'] > 80000) {
                $tablet_data['all_sales'] = 0;
            }

            // Подсчет общей стоимости продаж
            $price = str_replace(',', '.', $tablet_data['price']);
            $tablet_data['all_sales_price'] = floatval($price) * floatval($tablet_data['all_sales']);

            // Суммирование общих данных
            $this->tablets[1]['all_sales'] += $tablet_data['all_sales'];
            $this->tablets[1]['all_sales_price'] += $tablet_data['all_sales_price'];

            // Подсчет помесячных данных
            for ($i = 1; $i <= 12; $i++) {
                $this->tablets[1][$i] += $tablet_data[$i] ?? 0;
                if (!empty($this->tablets[1][$i]) && $this->tablets[1][$i] > 0) {
                    $this->tablets[1][$i + 20] += $tablet_data[$i + 20] ?? 0;
                }
            }

            $this->tablets[] = $tablet_data;
        }

        return collect($this->tablets);
    }

    private function processTablet($tablet): array
    {
        $results = [];

        $depo_list = in_array('all', $this->filter['depo'])
            ? array_keys($this->depo_models)
            : $this->filter['depo'];

        foreach ($depo_list as $depo) {
            if (!isset($this->depo_models[$depo])) {
                continue;
            }

            $model = $this->depo_models[$depo];
            $tablet_name = $tablet->$depo;
            if (empty($tablet_name)) {
                continue;
            }
            $orWhere = [];
            $where = [];
            if (is_array(json_decode($tablet->$depo, 1))) {
                foreach (json_decode($tablet->$depo, 1) as $tablet_name) {
                    $orWhere[] = [['tablet_name', '=', $tablet_name]];
                }
            } else {
                $where[] = ['tablet_name', '=', $tablet->$depo];
            }

            if ($depo === 'aztt') {
                if (count($orWhere) >= 1) {
                    $res = $model::where($orWhere[0]);
                    if (count($orWhere) > 1) {
                        foreach ($orWhere as $orwhere) {
                            if ($orWhere[0] != $orwhere) {
                                $res = $res->orWhere($orwhere);
                            }
                        }
                    }
                    $results[] = $res->get();
                    continue;
                } else {
                    $where[] = ['aptek_name', '!=', ''];
                    $results[] = $model::where($where)->get();
                    continue;
                }
            } elseif ($depo == 'radez' or $depo == 'zeytun') {
                if (count($orWhere) >= 1) {
                    $res = $model::where($orWhere[0]);
                    if (count($orWhere) > 1) {
                        foreach ($orWhere as $orwhere) {
                            if ($orWhere[0] != $orwhere) {
                                $res = $res->orWhere($orwhere);
                            }
                        }
                    }
                    $results[] = $res->get();
                    continue;
                } else {
                    $where[] = ['aptek_name', '=', null];
                    $results[] = $model::where($where)->get();
                    continue;
                }
            } elseif ($depo === 'epidbiomed') {
                $results[] = $model::where($where)->whereNull('region_name')->get();
                continue;
            }
            if (count($orWhere) >= 1) {
                $res = $model::where($orWhere[0]);
                if (count($orWhere) > 1) {
                    foreach ($orWhere as $orwhere) {
                        if ($orWhere[0] != $orwhere) {
                            $res = $res->orWhere($orwhere);
                        }
                    }
                }
                $results[] = $res->get();
            } else {
                $results[] = $model::where($where)->get();
            }
        }
        return $results;
    }

    public function getFile($data, $tablets)
    {
        // Инициализация ключей
        foreach (range(1, 13) as $i) {
            $data[$i] = $data[$i] ?? 0;
        }
        foreach (range(21, 32) as $i) {
            $data[$i] = $data[$i] ?? 0;
        }
        $data[13] = '';
        $price = floatval(str_replace(',', '.', $data['price']));

        foreach ($tablets as $tablet) {
            $fileQuery = UploadedFile::where('file_id', $tablet->uploaded_file_id);

            // Применение фильтров
            if (!empty($this->filter['from'])) {
                $fileQuery->where('uploaded_date', '>=', $this->filter['from']);
            }
            if (!empty($this->filter['to'])) {
                $fileQuery->where('uploaded_date', '<=', $this->filter['to']);
            }

            if ($fileQuery->exists()) {
                $file = $fileQuery->first();
                $month = $file->uploaded_date
                    ? Carbon::make($file->uploaded_date)->month
                    : Carbon::now()->month;
                $this->updateSalesData($data, $month, $tablet->sales_qty, $price);
            }
        }

        return $data;
    }

    /**
     * Обновляет данные о продажах.
     */
    private function updateSalesData(&$data, $month, $salesQty, $price)
    {
        $data[$month] += floatval($salesQty);
        $data[$month + 20] += floatval($salesQty) * $price;

        // Лимит продаж
        if ($data[$month] > 80000) {
            $data[$month] = 0;
            $data[$month + 20] = 0;
        }
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Supplies';
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

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
use App\Models\ZeytunData;
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
        'month' => 'Продажи количество',
        'sales_qty' => 'Общее количество продаж',
        'month_sales' => 'Продажи за этот месяц',
        'price_avg' => 'Общее количество продаж',
    ],1 => [
        'a' =>'',
        'tablet_name' => '',
        'sales_qty' => '',
        'month' => '',
        'month_sales' => ' ',
        'price_avg' => '',
    ]];

    /**
     * @return array
     */
    public function collection(): Collection
    {
        $sheets = [];
        $tablets = MainTabletMatrix::all();
        $regions = RegionMatrix::all();
        foreach ($tablets as $tablet) {
            $tablet_data = [];
            $month = AvromedData::where([['tablet_name', '=', $tablet->avromed],['aptek_name', '!=', '']])->orderBy('created_at', 'desc')->first();
            if ($month){
                $this->tablets[0]['month_sales'] = 'Продажи за '.$month->created_at->format('F');
                $this->tablets[0]['month'] = $month->created_at->format('F');
            }
            $tablet_data['a'] = '';
            $tablet_data['tablet_name'] = $tablet->mainname;
            $tablet_data['sales_qty'] = 0;
            $tablet_data['sales_qty'] += AvromedData::where([['tablet_name', '=', $tablet->avromed],['aptek_name', '!=', '']])->sum('sales_qty');
            $tablet_data['sales_qty'] += AzttData::where([['tablet_name', '=', $tablet->aztt],['aptek_name', '!=', '']])->sum('sales_qty');
            $tablet_data['sales_qty'] += EpidbiomedData::where([['tablet_name', '=', $tablet->epidbiomed],['aptek_name', '!=', '']])->sum('sales_qty');
            $tablet_data['sales_qty'] += AzerimedData::where([['tablet_name', '=', $tablet->azerimed],['aptek_name', '!=', '']])->sum('sales_qty');
            $tablet_data['sales_qty'] += PashaData::where([['tablet_name', '=', $tablet->pasha],['aptek_name', '!=', '']])->sum('sales_qty');
            $tablet_data['sales_qty'] += RadezData::where([['tablet_name', '=', $tablet->radez],['aptek_name', '!=', '']])->sum('sales_qty');
            $tablet_data['sales_qty'] += SonarData::where([['tablet_name', '=', $tablet->sonar],['aptek_name', '!=', '']])->sum('sales_qty');
            $tablet_data['sales_qty'] += ZeytunData::where([['tablet_name', '=', $tablet->zetun],['aptek_name', '!=', '']])->sum('sales_qty');
            $tablet_data['month'] = $tablet_data['sales_qty'];
            $tablet_data['month_sales'] = $tablet->price*$tablet_data['sales_qty'].'$';
            $tablet_data['price_avg'] = $tablet->price*$tablet_data['sales_qty'].'$';
            $this->tablets[] = $tablet_data;
        }

        foreach ($regions as $region) {
            $this->regions_array[0][] = $region->mainname;

            $tablet_data = [];
            foreach ($tablets as $tablet) {
                $month = AvromedData::where([['tablet_name', '=', $tablet->avromed],['aptek_name', '!=', '']])->orderBy('created_at', 'desc')->first();
                if ($month){
                    $this->tablets[0]['month_sales'] = 'Продажи за '.$month->created_at->format('F');
                    $this->tablets[0]['month'] = $month->created_at->format('F');
                }
                $tablet_data['a'] = '';
                $tablet_data['tablet_name'] = $tablet->mainname;
                $tablet_data['sales_qty'] = 0;
                $tablet_data['sales_qty'] += AvromedData::where([['tablet_name', '=', $tablet->avromed],['aptek_name', '!=', ''], ['region_name','=',$region->mainname]])->sum('sales_qty');
                $tablet_data['sales_qty'] += AzerimedData::where([['tablet_name', '=', $tablet->azerimed],['aptek_name', '!=', ''], ['region_name','=',$region->mainname]])->sum('sales_qty');
                $tablet_data['sales_qty'] += SonarData::where([['tablet_name', '=', $tablet->sonar],['aptek_name', '!=', ''], ['region_name','=',$region->mainname]])->sum('sales_qty');
                $tablet_data['sales_qty'] += ZeytunData::where([['tablet_name', '=', $tablet->zetun],['aptek_name', '!=', ''], ['region_name','=',$region->mainname]])->sum('sales_qty');
                $this->regions_array[][$region->mainname] = $tablet_data['sales_qty'];
            }
        }

        return collect($this->tablets);
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

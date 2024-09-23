<?php

namespace App\Exports;

use App\Models\AzttData;
use App\Models\AvromedData;
use App\Models\AzerimedData;
use App\Models\EpidbiomedData;
use App\Models\MainTabletMatrix;
use App\Models\PashaData;
use App\Models\RadezData;
use App\Models\SonarData;
use App\Models\ZeytunData;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TabletsExport implements FromCollection, ShouldQueue, ShouldAutoSize, WithStyles
{
    use Exportable;

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

    public function collection()
    {
        Carbon::setLocale('ru');
        $tablets = MainTabletMatrix::all();
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
        return collect($this->tablets);
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

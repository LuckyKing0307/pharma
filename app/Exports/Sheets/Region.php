<?php

namespace App\Exports\Sheets;

use App\Models\AvromedData;
use App\Models\AzerimedData;
use App\Models\MainTabletMatrix;
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
                $tablet_data[$count] += AvromedData::where([['tablet_name', '=', $tablet->avromed],['aptek_name', '!=', ''], ['region_name','=',$region->avromed]])->sum('sales_qty');
                $tablet_data[$count] += AzerimedData::where([['tablet_name', '=', $tablet->azerimed],['aptek_name', '!=', ''], ['region_name','=',$region->azerimed]])->sum('sales_qty');
                $tablet_data[$count] += SonarData::where([['tablet_name', '=', $tablet->sonar],['aptek_name', '!=', ''], ['region_name','=',$region->sonar]])->sum('sales_qty');
                $tablet_data[$count] += ZeytunData::where([['tablet_name', '=', $tablet->zetun],['aptek_name', '!=', ''], ['region_name','=',$region->zetun]])->sum('sales_qty');
                $this->regions_array[] = $tablet_data;
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

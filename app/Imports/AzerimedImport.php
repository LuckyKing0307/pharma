<?php

namespace App\Imports;

use App\Models\AzerimedData;
use App\Models\TabletMatrix;
use App\Models\UploadedFile;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;

class AzerimedImport implements ToModel, WithChunkReading, ShouldQueue,WithEvents, WithBatchInserts
{
    use RemembersRowNumber, Importable, RegistersEventListeners;

    public string $firm = 'azerimed';
    public string $file_id;
    public int $tabletNameRow = 3;

    public function __construct($file_id)
    {
        $this->file_id = $file_id;
    }

    public function model(array $row)
    {
        if (strtolower($row[0]) != 'müştəri adı' and $row[0] != '' and strtolower($row[3]) != 'miqdarı') {
            $region_name = $row[1] ? explode('|', $row[1])[0] : '';
            if ($region_name == 'NERIMANO' and $region_name == 'NARIMANO') {
                $region_name = 'NARIMAN';
            }
            AzerimedData::create([
                'region' => $row[1],
                'region_name' => $region_name,
                'aptek_name' => $row[0],
                'tablet_name' => $row[3],
                'sales_qty' => $row[4],
                'uploaded_file_id' => $this->file_id,
                'uploaded_date' => Carbon::now(),
            ]);
            info($row[3] . ' ' . $row[0]);
            $tablets = TabletMatrix::where(['avromed' => $row[$this->tabletNameRow]])
                ->orWhere(['azerimed' => $row[$this->tabletNameRow]])
                ->orWhere(['aztt' => $row[$this->tabletNameRow]])
                ->orWhere(['epidbiomed' => $row[$this->tabletNameRow]])
                ->orWhere(['pasha-k' => $row[$this->tabletNameRow]])
                ->orWhere(['radez' => $row[$this->tabletNameRow]])
                ->orWhere(['sonar' => $row[$this->tabletNameRow]])
                ->orWhere(['zeytun' => $row[$this->tabletNameRow]]);
            if (!$tablets->exists()) {
                TabletMatrix::create([
                    $this->firm => $row[$this->tabletNameRow],
                ]);
            } elseif ($row[$this->tabletNameRow] != 'Name') {
                $tablets->get();
                foreach ($tablets as $tablet) {
                    $tablet->update([$this->firm => $row[$this->tabletNameRow]]);
                }
            }
        }
    }

    public function chunkSize(): int
    {
        return 100;
    }

    public function batchSize(): int
    {
        return 100;
    }

    public static function afterImport(AfterImport $event)
    {
        $file = UploadedFile::where(['which_depo' => 'avromed'])->where(['uploaded' => 0]);
        if ($file->exists()) {
            foreach ($file->get() as $file) {
                $file->uploaded = 1;
                $file->save();
            }
        }
    }
}

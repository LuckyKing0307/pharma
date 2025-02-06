<?php

namespace App\Imports;

use App\Models\AvromedData;
use App\Models\TabletMatrix;
use App\Models\UploadedFile;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Facades\Excel;

class AvromedImport implements ToModel, WithChunkReading, ShouldQueue, WithEvents, WithBatchInserts
{
    use RemembersRowNumber, Importable, RegistersEventListeners;

    public string $firm = 'avromed';
    public string $file_id;
    public int $tabletNameRow = 7;

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */

    public function __construct($file_id)
    {
        $this->file_id = $file_id;
    }

    public function model(array $row)
    {

        $text = preg_replace('/\s*\([^)]*\)\s*/', ' ', $row[7]); // Удаляем скобки и пробелы вокруг
        $text = trim(preg_replace('/\s+/', ' ', $text));
        $text2 = str_replace('№', '№', $text);
        if (strtolower($row[0]) != 'date' and $row[1] != 'Total' and $row[0] != '' and strtolower($row[7]) != 'Name') {
            AvromedData::create([
                'branch' => $row[1],
                'date' => $row[0],
                'main_parent' => $row[2],
                'main_supplier' => $row[3],
                'region' => $row[4],
                'region_name' => $row[5] != null ? $row[5] : $row[4],
                'aptek_name' => $row[6],
                'tablet_name' => $text,
                'supervisor' => $row[8],
                'item_code' => $row[9],
                'client_code' => $row[10],
                'sales_qty' => $row[11],
                'new_sales' => $row[12],
                'uploaded_file_id' => $this->file_id,
//                'sale_date' => Carbon::make($row[0]),
                'uploaded_date' => Carbon::now(),
            ]);
            $tablets = TabletMatrix::where(['avromed' => $text2]);
            if (!$tablets->exists()) {
                TabletMatrix::create([
                $this->firm => $text2,
                ]);
            } elseif ($row[$this->tabletNameRow] != 'Name') {
                $tablets->get();
                foreach ($tablets as $tablet) {
                    $tablet->update([$this->firm => $text2]);
                }
            }
        }
    }

    public function chunkSize(): int
    {
        return 5000;
    }

    public function batchSize(): int
    {
        return 5000;
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

<?php

namespace App\Imports;

use App\Models\TabletMatrix;
use App\Models\UploadedFile;
use App\Models\ZeytunData;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\RemembersRowNumber;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\AfterImport;

class ZeytunImport implements ToModel, WithStartRow, ShouldQueue, WithChunkReading, WithEvents, WithBatchInserts
{
    use RemembersRowNumber, Importable, RegistersEventListeners;

    public string $firm = 'zeytun';
    public string $file_id;
    public int $tabletNameRow = 1;

    public function __construct($file_id)
    {
        $this->file_id = $file_id;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if (str_contains($row['1'], ' шт') or str_contains($row['1'], ' упак') or str_contains($row['1'], ' флак') or str_contains($row['1'], ' тюб')) {
            $check = ZeytunData::where([['tablet_name', '=', $row[1]], ['sales_qty', '=', $row[2]], ['uploaded_file_id', '=', $this->file_id]]);
            $tablet_named = str_replace('(***)', '', $row[1]);
            if (!$check->exists()) {
                ZeytunData::create([
                    'tablet_name' => $tablet_named,
                    'sales_qty' => $row[2],
                    'uploaded_date' => Carbon::now(),
                    'uploaded_file_id' => $this->file_id,
                ]);
            }
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
            } elseif ($row[$this->tabletNameRow] != 'Итог') {
                $tablets->get();
                foreach ($tablets as $tablet) {
                    $tablet->update([$this->firm => $row[$this->tabletNameRow]]);
                }
            }
        } else {
            $region_name = '';
            if (count(explode(' ', $row[1])) > 1) {
                $words = explode(' ', $row[1]);
                $result = null;
                for ($i = 1; $i < count($words); $i++) {
                    if (!is_numeric($words[$i])) {
                        $result = $words[$i];
                        break;
                    }
                }
                if ($result === null) {
                    $result = 'Нет подходящего слова'; // Если нет подходящего слова
                }
                $region_name = strtolower($result);
                $region_name = str_replace('ı', 'i', $region_name);
                $region_name = str_replace('Ə', 'a', $region_name);
                $region_name = str_replace('İ', 'a', $region_name);
            }
            if ($row[1] != 'Итог' and $row[1] != '') {
                $tablet = ZeytunData::where([['aptek_name', '=', null], ['uploaded_file_id', '=', $this->file_id]])->orderBy('created_at', 'desc')->limit(10);
                if ($tablet->exists()) {
                    $tablet = $tablet->first();
                    ZeytunData::create([
                        'aptek_name' => $row[1] != '' ? $row[1] : 'QWER',
                        'tablet_name' => $tablet->tablet_name,
                        'sales_qty' => $row[2],
                        'uploaded_date' => Carbon::now(),
                        'uploaded_file_id' => $this->file_id,
                        'region_name' => $region_name,
                    ]);
                }
            }
        }
    }

    public static function afterImport(AfterImport $event)
    {
        $file = UploadedFile::where(['which_depo' => 'zeytun'])->where(['uploaded' => 0]);
        if ($file->exists()) {
            foreach ($file->get() as $file) {
                $file->uploaded = 1;
                $file->save();
            }
        }
    }

    public function startRow(): int
    {
        return 12;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 1000;
    }
}

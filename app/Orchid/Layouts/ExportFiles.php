<?php

namespace App\Orchid\Layouts;

use App\Models\UploadedFile;
use Carbon\Carbon;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ExportFiles extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'files';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [

            TD::make('id', 'ID'),
            TD::make('uploaded_date', 'День загрузки')->render(function (\App\Models\ExportFiles $file){
                $created_at = Carbon::make($file->created_at)->format('F');
                return $created_at;
            }),
            TD::make('download', 'Скачать')->render(function (\App\Models\ExportFiles $file){
                return "<a href=".env('APP_FILE_URL').'/storage/'.$file->file_url.'.xlsx'." target='_blank'>download</a>";
            }),
            TD::make('Удалить')
                ->alignCenter()
                ->render(function (\App\Models\ExportFiles $file) {
                    return Button::make('Удалить файл')
                        ->confirm('After deleting, the task will be gone forever.')
                        ->method('delete', ['file' => $file->id]);
                }),
        ];
    }
}

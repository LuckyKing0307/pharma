<?php

namespace App\Orchid\Layouts;

use App\Models\UploadedFile;
use Carbon\Carbon;
use Orchid\Attachment\Models\Attachment;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class FileTable extends Table
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
            TD::make('uploaded_date', 'День загрузки')->render(function (UploadedFile $file){
                $created_at = $file->uploaded_date ? Carbon::make($file->uploaded_date)->format('F') : Carbon::make($file->created_at)->format('F');
                return $created_at;
            }),
            TD::make('download', 'Скачать')->render(function (UploadedFile $file){
                return Attachment::find($file->id) ? "<a href=".Attachment::find($file->id)->url()." target='_blank'>download</a>" : 'Удален';
            }),

            TD::make('Uploaded', 'Загружен')->render(function (UploadedFile $file){
                return $file->uploaded ? 'Yes' : 'No';
            }),
            TD::make('Удалить')
                ->alignCenter()
                ->render(function (UploadedFile $file) {
                    return Button::make('Удалить файл')
                        ->confirm('After deleting, the task will be gone forever.')
                        ->method('delete', ['file' => $file->id]);
                }),
        ];
    }
}

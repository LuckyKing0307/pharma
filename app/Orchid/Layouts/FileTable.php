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
            TD::make('created_at', 'Upload date')->render(function (UploadedFile $file){
                $created_at = Carbon::make($file->created_at)->format('Y-m-d');
                return $created_at;
            }),
            TD::make('download', 'MESSAGE')->render(function (UploadedFile $file){
                $link = Attachment::find($file->id)->url();
                return "<a href='{$link}' target='_blank'>download</a>";
            }),

            TD::make('Uploaded', 'uploaded')->render(function (UploadedFile $file){
                return $file->uploaded ? 'Yes' : 'No';
            }),
            TD::make('Delete')
                ->alignCenter()
                ->render(function (UploadedFile $file) {
                    return Button::make('Delete File')
                        ->confirm('After deleting, the task will be gone forever.')
                        ->method('delete', ['file' => $file->id]);
                }),
        ];
    }
}

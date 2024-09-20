<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Field;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Layouts\Rows;
use Orchid\Support\Facades\Layout;

class  UploadFile extends Rows
{
    /**
     * Used to create the title of a group of form elements.
     *
     * @var string|null
     */
    protected $title;

    /**
     * Get the fields elements to be displayed.
     *
     * @return Field[]
     */
    protected function fields(): iterable
    {
        return [
            Upload::make('file')
                ->title('Upload Photo')
                ->acceptedFiles('.xlsx,.xls')
                ->maxFiles(1)
                ->required(),
            DateTimer::make('date')
                ->title('Start At')->enableTime()
                ->required(),
        ];
    }
}

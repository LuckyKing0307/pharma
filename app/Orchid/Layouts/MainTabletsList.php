<?php

namespace App\Orchid\Layouts;

use App\Models\MainTabletMatrix;
use App\Models\UploadedFile;
use Carbon\Carbon;
use Orchid\Attachment\Models\Attachment;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class MainTabletsList extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'tablets';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('mainname', 'MainName'),
            TD::make('price', 'Price'),
            TD::make('avromed', 'Avromed'),
            TD::make('azerimed', 'Azerimed'),
            TD::make('aztt', 'Aztt'),
            TD::make('epidbiomed', 'Epidbiomed'),
            TD::make('pasha-k', 'Pasha k'),
            TD::make('radez', 'Radez'),
            TD::make('sonar', 'Sonar'),
            TD::make('zeytun', 'Zeytun'),
            TD::make('Edit')->render(function (MainTabletMatrix $tabletMatrix){
                return ModalToggle::make('Edit')
                    ->alignCenter()
                    ->modal('editTablet')
                    ->method('update')
                    ->modalTitle('Edit Tablet')
                    ->asyncParameters([
                        'tablet' => $tabletMatrix->id
                    ]);
            }),
            TD::make('Delete')
                ->alignCenter()
                ->render(function (MainTabletMatrix $tabletMatrix) {
                    return Button::make('Delete File')
                        ->confirm('After deleting, the task will be gone forever.')
                        ->method('delete', ['tablet' => $tabletMatrix->id]);
                }),
        ];
    }
}

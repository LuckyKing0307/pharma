<?php

namespace App\Orchid\Layouts;

use App\Models\TabletMatrix;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Layouts\Rows;

class MainTabletsEditRows extends Rows
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
            Input::make('tablet.id')
                ->hidden(),
            Input::make('tablet.mainname')
                ->title('Tablet Main Name')
                ->placeholder('Enter main tablet name')->required(),
            Input::make('tablet.price')
                ->title('Tablet Price')
                ->placeholder('Enter price')->required(),
            Relation::make('tablet.avromed')
                ->fromModel(TabletMatrix::class, 'avromed', 'avromed')
                ->title('Avromed'),
            Relation::make('tablet.azerimed')
                ->fromModel(TabletMatrix::class, 'azerimed', 'azerimed')
                ->title('Azerimed'),
            Relation::make('tablet.aztt')
                ->fromModel(TabletMatrix::class, 'aztt', 'aztt')
                ->title('Aztt'),
            Relation::make('tablet.epidbiomed')
                ->fromModel(TabletMatrix::class, 'epidbiomed', 'epidbiomed')
                ->title('Epidbiomed')->value('tablet.epidbiomed'),
            Relation::make('tablet.pasha-k')
                ->fromModel(TabletMatrix::class, 'pasha-k', 'pasha-k')
                ->title('Pasha-k'),
            Relation::make('tablet.radez')
                ->fromModel(TabletMatrix::class, 'radez', 'radez')
                ->title('Radez'),
            Relation::make('tablet.sonar')
                ->fromModel(TabletMatrix::class, 'sonar', 'sonar')
                ->title('Sonar')->value('tablet.sonar'),
            Relation::make('tablet.zeytun')
                ->fromModel(TabletMatrix::class, 'zeytun', 'zeytun')
                ->title('Zeytun'),
        ];
    }
}

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
            Relation::make('tablet.avromed')->nullable(1)
                ->fromModel(TabletMatrix::class, 'avromed', 'avromed')
                ->title('Avromed')->multiple(),
            Relation::make('tablet.azerimed')->nullable(1)
                ->fromModel(TabletMatrix::class, 'azerimed', 'azerimed')
                ->title('Azerimed')->multiple(),
            Relation::make('tablet.aztt')->nullable(1)
                ->fromModel(TabletMatrix::class, 'aztt', 'aztt')
                ->title('Aztt')->multiple(),
            Relation::make('tablet.epidbiomed')->nullable(1)
                ->fromModel(TabletMatrix::class, 'epidbiomed', 'epidbiomed')
                ->title('Epidbiomed')->value('tablet.epidbiomed')->multiple(),
            Relation::make('tablet.pasha-k')->nullable(1)
                ->fromModel(TabletMatrix::class, 'pasha-k', 'pasha-k')
                ->title('Pasha-k')->multiple(),
            Relation::make('tablet.radez')->nullable(1)
                ->fromModel(TabletMatrix::class, 'radez', 'radez')
                ->title('Radez')->multiple(),
            Relation::make('tablet.sonar')->nullable(1)
                ->fromModel(TabletMatrix::class, 'sonar', 'sonar')
                ->title('Sonar')->value('tablet.sonar')->multiple(),
            Relation::make('tablet.zeytun')->nullable(1)
                ->fromModel(TabletMatrix::class, 'zeytun', 'zeytun')
                ->title('Zeytun')->multiple(),
        ];
    }
}

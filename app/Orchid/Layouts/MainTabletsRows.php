<?php

namespace App\Orchid\Layouts;

use App\Models\TabletMatrix;
use Orchid\Screen\Field;
use Orchid\Screen\Layouts\Rows;

use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
class MainTabletsRows extends Rows
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
            Input::make('mainname')
                ->title('Tablet Main Name')
                ->placeholder('Enter main tablet name')->required(),
            Input::make('price')
                ->title('Tablet price')
                ->placeholder('Enter price')->required(),
            Relation::make('avromed')
                ->fromModel(TabletMatrix::class, 'avromed', 'avromed')
                ->title('Avromed'),
            Relation::make('azerimed')
                ->fromModel(TabletMatrix::class, 'azerimed', 'azerimed')
                ->title('Azerimed'),
            Relation::make('aztt')
                ->fromModel(TabletMatrix::class, 'aztt', 'aztt')
                ->title('Aztt'),
            Relation::make('epidbiomed')
                ->fromModel(TabletMatrix::class, 'epidbiomed', 'epidbiomed')
                ->title('Epidbiomed'),
            Relation::make('pasha-k')
                ->fromModel(TabletMatrix::class, 'pasha-k', 'pasha-k')
                ->title('Pasha-k'),
            Relation::make('radez')
                ->fromModel(TabletMatrix::class, 'radez', 'radez')
                ->title('Radez'),
            Relation::make('sonar')
                ->fromModel(TabletMatrix::class, 'sonar', 'sonar')
                ->title('Sonar'),
            Relation::make('zeytun')
                ->fromModel(TabletMatrix::class, 'zeytun', 'zeytun')
                ->title('Zeytun'),
        ];
    }
}

<?php

namespace App\Orchid\Layouts;

use App\Models\RegionMatrix;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Layouts\Rows;

class ExportCreate extends Rows
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
        $depos = [
            'all' => 'All',
            'avromed' => 'Avromed',
            'aztt' => 'Aztt',
            'azerimed' => 'Azerimed',
            'epidbiomed' => 'Epidbiomed',
            'pasha-k' => 'Pasha',
            'radez' => 'Radez',
            'sonar' => 'Sonar',
            'zeytun' => 'Zeytun',
        ];
        $region = RegionMatrix::all();
        $region_group = [];
        $region_group['all'] = 'All Regions';
        foreach ($region as $depo){
            $region_group[$depo->id] = $depo->mainname;
        }
        return [
            DateTimer::make('from')
                ->title('Начало дня выгрузки')->help('Начиная с какого дня будет выбирать файл загруженный в базу')
                ->required(),
            DateTimer::make('to')
                ->title('Последний день выгрузки')->help('Выберите день, который будет считаться последним днем для выбора файлов.')
                ->required(),
            Select::make('depo')
                ->options($depos)->multiple()
                ->title('Select depo')
                ->help('Allow search bots to index')->required(),
            Select::make('region')
                ->options($region_group)->multiple()
                ->title('Select region')
                ->help('Allow search bots to index')->required(),
            ];
    }
}

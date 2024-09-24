<?php

namespace App\Orchid\Layouts\Region;

use App\Models\AvromedData;
use App\Models\AzerimedData;
use App\Models\PashaData;
use App\Models\RadezData;
use App\Models\RegionMatrix;
use App\Models\SonarData;
use App\Models\TabletMatrix;
use App\Models\ZeytunData;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Layouts\Rows;

class RegionMatrixCreate extends Rows
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
                ->placeholder('Enter main region name')->required(),
            Input::make('price')
                ->title('Tablet price')
                ->placeholder('Enter price')->required(),
            Relation::make('avromed')
                ->fromModel(AvromedData::class, 'region_name', 'region_name')
                ->title('Avromed'),
            Relation::make('azerimed')
                ->fromModel(AzerimedData::class, 'region_name', 'region_name')
                ->title('Azerimed'),
            Relation::make('sonar')
                ->fromModel(SonarData::class, 'region_name', 'region_name')
                ->title('Sonar'),
            Relation::make('zeytun')
                ->fromModel(ZeytunData::class, 'region_name', 'region_name')
                ->title('Zeytun'),
        ];
    }
}

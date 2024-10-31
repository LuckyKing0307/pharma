<?php

namespace App\Orchid\Layouts\Region;

use App\Models\AvromedData;
use App\Models\AzerimedData;
use App\Models\AzttData;
use App\Models\EpidbiomedData;
use App\Models\PashaData;
use App\Models\RadezData;
use App\Models\SonarData;
use App\Models\TabletMatrix;
use App\Models\ZeytunData;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Layouts\Rows;

class RegionMatrixEdit extends Rows
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
            Input::make('region.id')
                ->hidden(),
            Input::make('region.mainname')
                ->title('Region Main Name')
                ->placeholder('Enter main tablet name')->required(),
            Input::make('region.price')
                ->title('Region Price')->value(0)->hidden()
                ->placeholder('Enter price')->required(),
            Relation::make('region.avromed')
                ->fromModel(AvromedData::class, 'region_name', 'region_name')
                ->title('Avromed')->nullable(),
            Relation::make('region.azerimed')
                ->fromModel(AzerimedData::class, 'region_name', 'region_name')
                ->title('Azerimed')->nullable(),
            Relation::make('region.sonar')
                ->fromModel(SonarData::class, 'region_name', 'region_name')
                ->title('Sonar')->nullable(),
            Relation::make('region.pasha-k')
                ->fromModel(PashaData::class, 'region_name', 'region_name')
                ->title('Pasha k')->nullable(),
            Input::make('region.radez')->title('Radez'),
            Input::make('region.epidbiomed')->title('Epidbiomed'),
            Relation::make('region.aztt')
                ->fromModel(AzttData::class, 'region_name', 'region_name')
                ->title('Aztt')->nullable(),
            Relation::make('region.zeytun')
                ->fromModel(ZeytunData::class, 'region_name', 'region_name')
                ->title('Zeytun')->nullable(),
            ];
    }
}

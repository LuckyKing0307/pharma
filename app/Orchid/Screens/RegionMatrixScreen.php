<?php

namespace App\Orchid\Screens;

use App\Models\MainTabletMatrix;
use App\Models\RegionMatrix;
use App\Orchid\Layouts\Region\RegionMatrixCreate;
use App\Orchid\Layouts\Region\RegionMatrixEdit;
use App\Orchid\Layouts\Region\RegionMatrixList;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class RegionMatrixScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'regions' => RegionMatrix::paginate(10),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Add New Region';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add')
                ->modal('createTablets')
                ->method('create')
                ->icon('plus'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            RegionMatrixList::class,
            Layout::modal('createTablets', RegionMatrixCreate::class)
                ->title('Create New Region')
                ->applyButton('Create'),
            Layout::modal('editregion', RegionMatrixEdit::class)->async('asyncGetTablet')
        ];
    }


    public function asyncGetTablet(RegionMatrix $region): array
    {
        return [
            'region' => $region
        ];
    }

    public function create(Request $request)
    {
        $supply = $request->all();
        unset($supply['_token']);
        unset($supply['_state']);
        $tab = new RegionMatrix($supply);
        $tab->save();
    }

    public function update(Request $request)
    {
        RegionMatrix::find($request->all()['region']['id'])->update($request->all()['region']);
    }

    public function delete(RegionMatrix $region)
    {
        $region->delete();
    }
}

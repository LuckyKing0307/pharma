<?php

namespace App\Orchid\Screens;

use App\Models\MainTabletMatrix;
use App\Orchid\Layouts\MainTabletsEditRows;
use App\Orchid\Layouts\MainTabletsList;
use App\Orchid\Layouts\MainTabletsRows;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class MainTabletScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'tablets' => MainTabletMatrix::paginate(10),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Add Supplies';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add File')
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
            MainTabletsList::class,
            Layout::modal('createTablets', MainTabletsRows::class)
                ->title('Create New Supplies')
                ->applyButton('Create'),
            Layout::modal('editTablet', MainTabletsEditRows::class)->async('asyncGetTablet')
        ];
    }


    public function asyncGetTablet(MainTabletMatrix $tablet): array
    {
        return [
            'tablet' => $tablet
        ];
    }

    public function create(Request $request)
    {
        $supply = $request->all();
        unset($supply['_token']);
        unset($supply['_state']);
        $tab = new MainTabletMatrix($supply);
        $tab->save();
    }

    public function update(Request $request)
    {
        MainTabletMatrix::find($request->all()['tablet']['id'])->update($request->all()['tablet']);
    }

    public function delete(MainTabletMatrix $tablet)
    {
        $tablet->delete();
    }
}

<?php

declare(strict_types=1);

namespace App\Orchid;

use Orchid\Platform\Dashboard;
use Orchid\Platform\ItemPermission;
use Orchid\Platform\OrchidServiceProvider;
use Orchid\Screen\Actions\Menu;
use Orchid\Support\Color;

class PlatformProvider extends OrchidServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param Dashboard $dashboard
     *
     * @return void
     */
    public function boot(Dashboard $dashboard): void
    {
        parent::boot($dashboard);

        // ...
    }

    /**
     * Register the application menu.
     *
     * @return Menu[]
     */
    public function menu(): array
    {
        return [
//            Menu::make('Get Started')
//                ->icon('bs.book')
//                ->title('Navigation')
//                ->route(config('platform.index')),
//
//            Menu::make('Sample Screen')
//                ->icon('bs.collection')
//                ->route('platform.example')
//                ->badge(fn () => 6),
//
//            Menu::make('Form Elements')
//                ->icon('bs.card-list')
//                ->route('platform.example.fields')
//                ->active('*/examples/form/*'),
//
//            Menu::make('Overview Layouts')
//                ->icon('bs.window-sidebar')
//                ->route('platform.example.layouts'),
//
//            Menu::make('Grid System')
//                ->icon('bs.columns-gap')
//                ->route('platform.example.grid'),
//
//            Menu::make('Charts')
//                ->icon('bs.bar-chart')
//                ->route('platform.example.charts'),
//
//            Menu::make('Cards')
//                ->icon('bs.card-text')
//                ->route('platform.example.cards')
//                ->divider(),

            Menu::make(__('Users'))
                ->icon('bs.people')
                ->route('platform.systems.users')
                ->permission('platform.systems.users')
                ->title(__('Access Controls')),

            Menu::make(__('Roles'))
                ->icon('bs.shield')
                ->route('platform.systems.roles')
                ->permission('platform.systems.roles')
                ->divider(),

            Menu::make('ADD')
                ->title('Tablets')
                ->icon('bs.activity')
                ->route('tablets.main'),

            Menu::make('Avromed')
                ->title('Companies')
                ->icon('bs.archive-fill')
                ->route('company.avromed'),

            Menu::make('Azerimed')
                ->icon('bs.archive-fill')
                ->route('company.azerimed'),

            Menu::make('Aztt')
                ->icon('bs.archive-fill')
                ->route('company.aztt'),

            Menu::make('Epidbiomed')
                ->icon('bs.archive-fill')
                ->route('company.epidbiomed'),

            Menu::make('Pasha-K')
                ->icon('bs.archive-fill')
                ->route('company.pasha'),

            Menu::make('Radez')
                ->icon('bs.archive-fill')
                ->route('company.radez'),

            Menu::make('Sonar')
                ->icon('bs.archive-fill')
                ->route('company.sconar'),

            Menu::make('Zeytun')
                ->icon('bs.archive-fill')
                ->route('company.zeytun'),

//            Menu::make('Matrix')
//                ->title('Matrix')
//                ->icon('bs.window-sidebar')
//                ->route('matrix.matrix'),
            Menu::make('Export Data')
                ->title('Export')
                ->icon('bs.window-sidebar')
                ->route('export.export'),
        ];
    }

    /**
     * Register permissions for the application.
     *
     * @return ItemPermission[]
     */
    public function permissions(): array
    {
        return [
            ItemPermission::group(__('System'))
                ->addPermission('platform.systems.roles', __('Roles'))
                ->addPermission('platform.systems.users', __('Users')),
        ];
    }
}

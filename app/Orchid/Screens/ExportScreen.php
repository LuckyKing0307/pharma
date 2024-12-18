<?php

namespace App\Orchid\Screens;

use App\Exports\TabletsExport;
use App\Jobs\ProcessPodcast;
use App\Models\ExportFiles;
use App\Orchid\Layouts\ExportCreate;
use App\Orchid\Layouts\ExportFiles as Table;
use App\Orchid\Layouts\UploadFile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Orchid\Alert\Alert;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

class ExportScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'files' => ExportFiles::where('uploaded',1)->orderBy('id', 'DESC')->paginate(10),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Экспорт Данных';
    }
    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {

        return [
//            Button::make('Create File')->method('download')->icon('plus'),
            ModalToggle::make('Create File')
                ->modal('uploadFile')
                ->method('download')
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
            Table::class,
            Layout::modal('uploadFile', ExportCreate::class)
                ->title('Upload File')
                ->applyButton('Upload'),
        ];
    }

    public function delete(ExportFiles $file)
    {
        ExportFiles::where(['id' => $file->id])->delete();
        $file->delete();
    }

    public function download(Request $request)
    {

        $data = $request->all();
        $file = new ExportFiles([
            'file_url' => Carbon::now()->format('Y-m-d'),
        ]);
        $file->save();
        $file->file_url = $file->file_url.'-'.$file->id;
        $file->save();
        var_dump($file->file_url);
        Toast::success('Started to generate excel file');
        ProcessPodcast::dispatch($file->id,$data);
//        $export->handle();
    }
}

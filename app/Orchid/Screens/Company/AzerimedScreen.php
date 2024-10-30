<?php

namespace App\Orchid\Screens\Company;

use App\Imports\AzerimedImport;
use App\Models\AzerimedData;
use App\Models\RegionMatrix;
use App\Models\UploadedFile;
use App\Orchid\Layouts\FileTable;
use App\Orchid\Layouts\UploadFile;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Attachment\Models\Attachment;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class AzerimedScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'files' => UploadedFile::where(['which_depo' => 'azerimed'])->paginate(10),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Azerimed Upload File';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make('Upload All Updates')->method('upload'),
            ModalToggle::make('Add File')
                ->modal('uploadFile')
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
            FileTable::class,
            Layout::modal('uploadFile', UploadFile::class)
                ->title('Upload File')
                ->applyButton('Upload'),
        ];
    }

    public function create(Request $request){
        $file = UploadedFile::create();
        $fileIds = $request->input('file', []) ? $request->input('file', []) : [];
        foreach ($fileIds as $fileId) {
            $attachment = Attachment::find($fileId);
            if ($attachment) {
                $file->which_depo = 'azerimed';
                $file->file_url = str_replace('/storage','app/public' , Attachment::find($fileId)->getRelativeUrlAttribute());
                $file->file_id = $attachment->id;
                $file->uploaded_date = $request->input('date');
            }
        }
        $file->save();
    }

    public function upload(Request $request)
    {
        $file = UploadedFile::where([['which_depo', '=', 'azerimed'],['uploaded', '=', 0]]);
        if ($file->exists()){
            foreach ($file->get() as $file){
                info('aaaaaaaaaaaaaaaaaaaa');
                info($file->file_id);
                Excel::import(new AzerimedImport($file->file_id), storage_path($file->file_url));
                $file->uploaded = 1;
                $file->save();
            }
        }
    }

    public function delete(UploadedFile $file)
    {
//        AzerimedData::where(['uploaded_file_id' => $file->id])->delete();
//        $file->delete();
        $regions = AzerimedData::all();
        foreach ($regions as $region){
            $regio_matrix = RegionMatrix::where(['azerimed' => $region->region_name]);
            $region_name = explode('|', $region->region_name)[0];
            if ($region_name=='NERIMANO'){
                $region_name = 'NARIMAN';
            }

            if ($regio_matrix->exists()){
                foreach ($regio_matrix->get() as $regio_m){
                    $regio_m->azerimed = $region_name;
                    $regio_m->save();
                }
            }
            $region->region_name = $region_name;
            $region->save();
        }
    }
}

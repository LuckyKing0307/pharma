<?php

namespace App\Http\Controllers;

use App\Exports\DepoExport;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TabletsExport;

class ExportController extends Controller
{
    public function export()
    {
        $day = Carbon::now()->format('Y-m-d');
        return Excel::download(new TabletsExport, $day.'.xlsx');
    }

    public function avromed()
    {
        $day = Carbon::now()->format('Y-m-d');
        return Excel::download(new DepoExport, 'Avromed '.$day.'.xlsx');
    }
}

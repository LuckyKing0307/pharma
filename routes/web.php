<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('export', [\App\Http\Controllers\ExportController::class, 'export']);
Route::get('depo/{depo:name}', [\App\Http\Controllers\ExportController::class, 'avromed']);

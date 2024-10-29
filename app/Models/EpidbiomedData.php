<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class EpidbiomedData extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'aptek_name',
        'tablet_name',
        'qty',
        'sales_qty',
        'ost_qty',
        'uploaded_file_id',
        'uploaded_date',
        'region_name'
    ];
}

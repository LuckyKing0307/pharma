<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class AzerimedData extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'date',
        'region',
        'region_name',
        'aptek_name',
        'tablet_name',
        'sales_qty',
        'uploaded_file_id',
        'sale_date',
        'uploaded_date',
    ];
}

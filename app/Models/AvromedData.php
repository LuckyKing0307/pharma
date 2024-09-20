<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class AvromedData extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'branch',
        'date',
        'main_parent',
        'main_supplier',
        'region',
        'region_name',
        'aptek_name',
        'tablet_name',
        'supervisor',
        'item_code',
        'client_code',
        'sales_qty',
        'new_sales',
        'uploaded_file_id',
        'sale_date',
        'uploaded_date',
    ];
}

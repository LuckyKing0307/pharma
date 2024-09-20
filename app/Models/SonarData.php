<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class SonarData extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'aptek_name',
        'tablet_name',
        'region',
        'region_name',
        'sales_qty',
        'uploaded_file_id',
        'uploaded_date'
    ];
}

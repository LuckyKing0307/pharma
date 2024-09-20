<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class UploadedFile extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'which_depo',
        'file_url',
        'file_id',
        'uploaded',
        'deleted',
        'uploaded_date',
    ];
}

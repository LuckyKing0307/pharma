<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class RegionMatrix extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'mainname',
        'price',
        'avromed',
        'azerimed',
        'aztt',
        'epidbiomed',
        'pasha-k',
        'radez',
        'sonar',
        'zeytun',
    ];
}

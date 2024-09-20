<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class TabletMatrix extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
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

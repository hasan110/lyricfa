<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    use HasFactory;

    protected $casts = [
        'id_singer_music_album' => 'integer',
        'type' => 'integer',
        'show_it' => 'integer',
    ];
}

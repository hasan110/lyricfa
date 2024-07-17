<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    use HasFactory;

    public function singers()
    {
        return $this->belongsToMany(Singer::class, 'album_singer', 'album_id', 'singer_id');
    }
}

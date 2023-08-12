<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Music;

class CommentMusic extends Model
{
    use HasFactory;
    protected $table = 'comment_musics';

    public function music()
    {
        return $this->belongsTo(Music::class , 'id_song');
    }
}

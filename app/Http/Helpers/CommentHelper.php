<?php

namespace App\Http\Helpers;

use App\Models\Music;

class CommentHelper extends Helper
{
    public function getMusicCommentsCount($music_id) :int
    {
        $music = Music::where('id', $music_id)->first();
        return $music ? $music->comments()->where("status" , 1)->count() : 0;
    }
}

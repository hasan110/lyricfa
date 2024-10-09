<?php

namespace App\Http\Helpers;

use App\Models\Music;
use App\Models\Singer;

class LikeHelper extends Helper
{
    public function getMusicLikesCount($music_id) :int
    {
        $music = Music::where('id', $music_id)->first();
        return $music ? $music->likes()->count() : 0;
    }

    public function isUserLikeMusic($music_id, $user_id)
    {
        $music = Music::where('id', $music_id)->first();
        return $music ? $music->likes()->where('user_id', $user_id)->count() : 0;
    }

    public function isUserLikeSinger($singer_id, $user_id)
    {
        $singer = Singer::where('id', $singer_id)->first();
        return $singer ? $singer->likes()->where('user_id', $user_id)->count() : 0;
    }
}

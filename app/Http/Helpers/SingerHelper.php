<?php

namespace App\Http\Helpers;

use App\Models\Music;
use App\Models\Singer;

class SingerHelper extends Helper
{
    public function getMusicSingers($music_id)
    {
        $music = Music::find($music_id);
        if (!$music) {
            return [];
        }
        return $music->singers;
    }

    public function singerLikesCount($singer_id)
    {
        $singer = Singer::where('id', $singer_id)->first();
        return $singer ? $singer->likes()->count() : 0;
    }
}

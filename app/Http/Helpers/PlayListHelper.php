<?php

namespace App\Http\Helpers;

use App\Models\Music;
use App\Models\PlayList;
use App\Models\PlayListMusic;

class PlayListHelper extends Helper
{
    public function getMusicSingers($music_id) :array
    {
        $music = Music::find($music_id);
        if (!$music) {
            return [];
        }
        return $music->singers;
    }

    public function getPlaylistByName($name , $user_id)
    {
        return PlayList::where('name', $name)->where('user_id', $user_id)->first();
    }

    public function checkMusicExistsInPlayList($playlist_id, $music_id)
    {
        return PlayListMusic::where('music_id', $music_id)->where('playlist_id', $playlist_id)->first();
    }
}

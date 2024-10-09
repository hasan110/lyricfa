<?php

namespace App\Http\Helpers;

use App\Http\Controllers\CommentMusicController;
use App\Http\Controllers\LikeMusicController;
use App\Http\Controllers\ScoreMusicController;
use App\Http\Controllers\SingerController;
use App\Models\ScoreMusic;

class MusicHelper extends Helper
{
    public function prepareMusicsTemplate($musics , $user_id = null, $in_playlist_ids = []) :array
    {
        $musics_array = [];
        foreach ($musics as $music) {
            $musics_array[] = $this->musicTemplate($music, $user_id, $in_playlist_ids);
        }

        return $musics_array;
    }

    public function musicTemplate($music , $user_id = null, $in_playlist_ids = []) :array
    {
        $singers = (new SingerHelper())->getMusicSingers($music->id);
        $num_like = (new LikeHelper())->getMusicLikesCount($music->id);
        $num_comment = (new CommentHelper())->getMusicCommentsCount($music->id);
        $average_score = $this->averageMusicScore($music->id);

        if ($user_id) {
            $user_like_it = (new LikeHelper())->isUserLikeMusic($music->id, $user_id);
        } else {
            $user_like_it = 0;
        }

        if (!empty($in_playlist_ids) && in_array($music->id, $in_playlist_ids)) {
            $music->in_playlist = 1;
        } else {
            $music->in_playlist = 0;
        }

        return [
            'music' => $music,
            'singers' => $singers,
            'num_like' => $num_like,
            'readable_like' => $this->readableNumber($num_like),
            'readable_views' => $this->readableNumber(intval($music->views)),
            'num_comment' => $num_comment,
            'readable_comment' => $this->readableNumber($num_comment),
            'user_like_it' => $user_like_it,
            'average_score' => +number_format($average_score,1)
        ];
    }

    public function averageMusicScore($music_id)
    {
        $scoreMusic = ScoreMusic::where('music_id', $music_id);
        if ($scoreMusic)
            return ScoreMusic::where('music_id', $music_id)->avg('score');
        else
            return 0;
    }

    public function countMusicScore($music_id)
    {
        return ScoreMusic::where('music_id', $music_id)->count();
    }

    public function getUserScoreMusic($user_id, $music_id)
    {
        return ScoreMusic::where('music_id', $music_id)->where('user_id', $user_id)->first();
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Film;
use App\Models\Music;
use App\Models\Setting;
use App\Models\Singer;
use App\Models\Slider;


class SettingController extends Controller
{

    public function getSetting()
    {
        $settings = Setting::query()->where('is_public' , '=' ,1)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        $response = [
            'data' => $result,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد"
        ];
        return response()->json($response);
    }

    public function getHomePageData()
    {
        $sliders = Slider::where('show_it', '=', 1)->orderBy("id")->get();
        $recent_musics = Music::orderBy('id', 'DESC')->take(40)->whereStatus(1)->get();
        $shuffled_recent_musics = $recent_musics->shuffle()->take(20);
        $most_viewed_musics = Music::orderBy('views', 'DESC')->take(20)->whereStatus(1)->get();

        $singers = Singer::take(20)->inRandomOrder()->get();
        $singer_list = [];
        foreach ($singers as $singer) {
            $num_like = LikeSingerController::getNumberSingerLike($singer->id);
            $singer_list[] = [
                'singer' => $singer,
                'num_like' => $num_like,
                'readable_like' => $this->getReadableNumber(intval($num_like)),
                'num_comment' => CommentSingerController::getNumberSingerComment($singer->id),
                'user_like_it' => 0
            ];
        }

        $albums = Album::orderBy('id', 'DESC')->take(10)->get();
        $films = Film::orderBy('id', "DESC")->whereIn('type', [1, 2])->get();

        $data = [
            'sliders' => $sliders,
            'recent_musics' => $this->prepareMusicsTemplate($shuffled_recent_musics),
            'most_viewed_musics' => $this->prepareMusicsTemplate($most_viewed_musics),
            'singers' => $singer_list,
            'albums' => $albums,
            'films' => $films,
        ];
        return response()->json([
            'data' => $data,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function prepareMusicsTemplate($musics) :array
    {
        $musics_array = [];
        foreach ($musics as $music) {
            $singer = SingerController::getSingerById($music->id);
            $num_like = LikeMusicController::getNumberMusicLike($music->id);
            $num_comment = CommentMusicController::getNumberMusicComment($music->id);
            $average_score = ScoreMusicController::getAverageMusicScore($music->id);
            $data = [
                'music' => $music,
                'singers' => $singer,
                'num_like' => $num_like,
                'readable_like' => $this->getReadableNumber(intval($num_like)),
                'num_comment' => $num_comment,
                'user_like_it' => 0,
                'average_score' => +number_format($average_score,1)
            ];
            $musics_array[] = $data;
        }

        return $musics_array;
    }
}

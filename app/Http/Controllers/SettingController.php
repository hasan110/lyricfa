<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Helpers\MusicHelper;
use App\Http\Helpers\SingerHelper;
use App\Models\Album;
use App\Models\Film;
use App\Models\Music;
use App\Models\Setting;
use App\Models\Singer;
use App\Models\Slider;
use Illuminate\Support\Facades\Cache;

class SettingController extends Controller
{
    public function getSetting()
    {
        return response()->json([
            'data' => Setting::fetch(false),
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد"
        ]);
    }

    public function getHomePageData()
    {
        $data = Cache::remember('home_page_data', 60 * 60, function () {
            $sliders = Slider::where('show_it', '=', 1)->orderBy("id")->get();
            $free_musics = Music::where('permission_type', 'free')->take(24)->whereStatus(1)->inRandomOrder()->get();
            $recent_musics = Music::orderBy('id', 'DESC')->take(40)->whereStatus(1)->get();
            $shuffled_recent_musics = $recent_musics->shuffle()->take(20);

            $most_viewed_musics = Music::where('permission_type' , 'paid')->whereStatus(1)->where('views' , '>' , 500)->inRandomOrder()->take(24)->get();

            $singers = Singer::take(20)->inRandomOrder()->get();
            $singer_list = [];
            foreach ($singers as $singer) {
                $num_like = (new SingerHelper())->singerLikesCount($singer->id);
                $singer_list[] = [
                    'singer' => $singer,
                    'num_like' => $num_like,
                    'readable_like' => (new Helper())->readableNumber(intval($num_like)),
                    'num_comment' => 0,
                    'user_like_it' => 0
                ];
            }

            $albums = Album::inRandomOrder()->take(24)->get();
            $films = Film::whereIn('type', [1, 2])->where('status' , 1)->inRandomOrder()->take(24)->get();
            $free_films = Film::orderBy('id', "DESC")->whereIn('type', [1, 2])->where('status' , 1)->whereIn('permission_type' , ['free','first_season_free','first_episode_free'])->take(24)->get();

            return [
                'sliders' => $sliders,
                'recent_musics' => (new MusicHelper())->prepareMusicsTemplate($shuffled_recent_musics),
                'most_viewed_musics' => (new MusicHelper())->prepareMusicsTemplate($most_viewed_musics),
                'free_musics' => (new MusicHelper())->prepareMusicsTemplate($free_musics),
                'singers' => $singer_list,
                'albums' => $albums,
                'films' => $films,
                'free_films' => $free_films,
            ];
        });

        return response()->json([
            'data' => $data,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }
}

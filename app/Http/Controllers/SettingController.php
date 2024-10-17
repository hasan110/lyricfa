<?php

namespace App\Http\Controllers;

use App\Http\Helpers\MusicHelper;
use App\Http\Helpers\SingerHelper;
use App\Models\Album;
use App\Models\Film;
use App\Models\Music;
use App\Models\Setting;
use App\Models\Singer;
use App\Models\Slider;
use App\Models\View;
use Carbon\Carbon;

class SettingController extends Controller
{
    public function getSetting()
    {
        $settings = Setting::query()->where('is_public' , '=' ,1)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        return response()->json([
            'data' => $result,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد"
        ]);
    }

    public function getHomePageData()
    {
        $sliders = Slider::where('show_it', '=', 1)->orderBy("id")->get();
        $recent_musics = Music::orderBy('id', 'DESC')->take(40)->whereStatus(1)->get();
        $shuffled_recent_musics = $recent_musics->shuffle()->take(20);

        $views = View::selectRaw('viewable_id , COUNT(*) AS cnt')->where('viewable_type',Music::class)->where('created_at', '>' , Carbon::now()->subWeek()->format("Y-m-d H:i:s"))->groupBy("viewable_id")->orderBy("cnt","desc")->limit(24)->get();
        $most_viewed_ids = array_column($views->toArray(),'viewable_id');
        $most_viewed_musics = Music::whereIn('id' , $most_viewed_ids)->orderByRaw('FIELD(id, '.implode(',' , $most_viewed_ids).')')->get();

        $singers = Singer::take(20)->inRandomOrder()->get();
        $singer_list = [];
        foreach ($singers as $singer) {
            $num_like = (new SingerHelper())->singerLikesCount($singer->id);
            $singer_list[] = [
                'singer' => $singer,
                'num_like' => $num_like,
                'readable_like' => $this->getReadableNumber(intval($num_like)),
                'num_comment' => 0,
                'user_like_it' => 0
            ];
        }

        $albums = Album::orderBy('id', 'DESC')->take(10)->get();
        $films = Film::orderBy('id', "DESC")->whereIn('type', [1, 2])->where('status' , 1)->take(20)->get();

        $data = [
            'sliders' => $sliders,
            'recent_musics' => (new MusicHelper())->prepareMusicsTemplate($shuffled_recent_musics),
            'most_viewed_musics' => (new MusicHelper())->prepareMusicsTemplate($most_viewed_musics),
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
}

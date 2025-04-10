<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Helpers\MusicHelper;
use App\Http\Helpers\SingerHelper;
use App\Http\Helpers\UserHelper;
use App\Models\Film;
use App\Models\Music;
use App\Models\Singer;
use App\Models\Slider;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function appData(Request $request)
    {
        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));
        $sliders = Slider::where('status', 1)->orderBy("updated_at", "desc")->get();
        $notifications = [];
        $latest_views = (new UserHelper())->getLatestViews($user);

        $data = [
            'sliders' => $sliders,
            'notifications' => $notifications,
            'latest_views' => $latest_views,
        ];

        return response()->json([
            'data' => $data,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function search(Request $request)
    {
        $search_text = $request->input('search_text');
        if (!$search_text || strlen($search_text) > 50) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "اطلاعات با موفقیت گرفته شد",
            ]);
        }

        $musics = Music::orderBy('views', 'DESC')->
            where("status", 1)->
            where('name', 'LIKE', "%{$search_text}%")->
            orWhere('persian_name', 'LIKE', "%{$search_text}%")->
            orWhere('id', $search_text)
        ->take(5)->get();

        $singers = Singer::where('english_name', 'LIKE', "%{$search_text}%")->orWhere('persian_name', 'LIKE', "%{$search_text}%")->take(5)->get();
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

        $films = Film::whereIn('type', [1, 2])->where('status' , 1)->where('english_name', 'LIKE', "%{$search_text}%")->orWhere('persian_name', 'LIKE', "%{$search_text}%")->take(5)->get();

        $data = [
            'musics' => (new MusicHelper())->prepareMusicsTemplate($musics),
            'singers' => $singer_list,
            'films' => $films,
            'categories' => [],
        ];

        return response()->json([
            'data' => $data,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }
}

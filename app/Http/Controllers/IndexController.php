<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Helpers\MusicHelper;
use App\Http\Helpers\SingerHelper;
use App\Http\Helpers\UserHelper;
use App\Models\Category;
use App\Models\Film;
use App\Models\Music;
use App\Models\Singer;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

        $musics = Music::orderBy('views', 'DESC')->where("status", 1)->where(function ($query) use ($search_text) {
            $query->where('name', 'LIKE', "%{$search_text}%")->
            orWhere('persian_name', 'LIKE', "%{$search_text}%")->
            orWhere('id', $search_text);
        })->take(5)->get();

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

        $films = Film::whereIn('type', [1, 2])->where('status' , 1)->where(function ($query) use ($search_text) {
            $query->where('english_name', 'LIKE', "%{$search_text}%")->orWhere('persian_name', 'LIKE', "%{$search_text}%");
        })->take(5)->get();

        $categories = Category::where('status', 1)->where('mode', 'category')->where('is_parent', 1)->where(function ($query) use ($search_text) {
            $query->where('title', 'LIKE', "%{$search_text}%")->orWhere('subtitle', 'LIKE', "%{$search_text}%");
        })->take(5)->get();

        $data = [
            'musics' => (new MusicHelper())->prepareMusicsTemplate($musics),
            'singers' => $singer_list,
            'films' => $films,
            'categories' => $categories,
        ];

        return response()->json([
            'data' => $data,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function media()
    {
        $data = Cache::remember('media_data', 60 * 60 * 6, function () {
            $free_musics = Music::where('permission_type', 'free')->take(24)->whereStatus(1)->inRandomOrder()->get();
            $recent_musics = Music::orderBy('id', 'DESC')->take(40)->whereStatus(1)->get();
            $shuffled_recent_musics = $recent_musics->shuffle()->take(20);
            $most_viewed_musics = Music::where('permission_type' , 'paid')->whereStatus(1)->where('views' , '>' , 500)->inRandomOrder()->take(24)->get();
            $free_films = Film::orderBy('id', "DESC")->whereIn('type', [1, 2])->where('status' , 1)->whereIn('permission_type' , ['free','first_season_free','first_episode_free'])->take(24)->get();

            $categories = Category::where('mode' , 'category')->where('is_public' , 1)->where('status' , 1)
                ->whereIn('belongs_to' , ['musics' , 'films'])
                ->with(['musics' , 'films' , 'children'])
                ->orderBy("priority")->get();

            foreach ($categories as $category) {
                $category->items = collect()->merge($category->musics)->merge($category->films);
            }

            return [
                'recent_musics' => (new MusicHelper())->prepareMusicsTemplate($shuffled_recent_musics),
                'most_viewed_musics' => (new MusicHelper())->prepareMusicsTemplate($most_viewed_musics),
                'free_musics' => (new MusicHelper())->prepareMusicsTemplate($free_musics),
                'free_films' => $free_films,
                'categories' => $categories,
            ];
        });

        return response()->json([
            'data' => $data,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function dictionary()
    {
        $data = Cache::remember('dictionary_data', 60 * 60 * 6, function () {
            $list = Category::where('mode' , 'category')->where('is_public' , 1)->where('status' , 1)
                ->where('belongs_to' , 'word_definitions')
                ->with(['children'])
                ->orderBy("priority")->get();

            return $list;
        });

        return response()->json([
            'data' => $data,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function grammar()
    {
        $data = Cache::remember('grammar_data', 60 * 60 * 6, function () {
            $list = Category::where('mode' , 'category')->where('is_public' , 1)->where('status' , 1)
                ->where('belongs_to' , 'grammers')
                ->with(['children'])
                ->orderBy("priority")->get();

            return $list;
        });

        return response()->json([
            'data' => $data,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }
}

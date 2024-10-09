<?php

namespace App\Http\Controllers;

use App\Http\Helpers\MusicHelper;
use App\Http\Helpers\UserHelper;
use App\Models\Music;
use App\Models\Singer;
use App\Models\Text;
use App\Models\View;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MusicController extends Controller
{
    public function getNRequestedMusic(Request $request)
    {
        $musics = Music::where('is_user_request', 1)->where("status", 1)->take(24)->inRandomOrder()->get();

        $data = (new MusicHelper())->prepareMusicsTemplate($musics);

        return response()->json([
            'data' => $data,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getRequestedMusic(Request $request)
    {
        $search_key = $request->search_text;
        $per_page = 24;

        $musics = Music::orderBy('id', "DESC")->where('is_user_request', 1)->where("status", 1);
        $musics = $musics->where(function ($query) use ($search_key) {
            $query->where('name', 'like', '%' . $search_key . '%')
                ->orWhere('persian_name', 'like', '%' . $search_key . '%');
        })->paginate($per_page);

        $list = (new MusicHelper())->prepareMusicsTemplate($musics);
        $last_page = $musics->lastPage();
        $total = $musics->total();

        return response()->json([
            'data' => [
                'data' => $list,
                'last_page' => $last_page,
                'per_page' => $per_page,
                'total' => $total,
            ],
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getMusicList(Request $request)
    {
        $search_text = $request->input('search_text');
        $per_page = 24;

        $musics = Music::orderBy('views', 'DESC')->
            where("status", 1)->
            where('name', 'LIKE', "%{$search_text}%")->
            orWhere('persian_name', 'LIKE', "%{$search_text}%")->
            orWhere('id', $search_text)
        ->paginate($per_page);

        $list = (new MusicHelper())->prepareMusicsTemplate($musics);
        $last_page = $musics->lastPage();
        $total = $musics->total();

        return response()->json([
            'data' => [
                'data' => $list,
                'last_page' => $last_page,
                'per_page' => $per_page,
                'total' => $total,
            ],
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getMusics(Request $request)
    {
        $search_text = $request->input('search_text');

        $sort = $request->input('sort');
        $available_sort = [
            'most_viewed' => 'views',
            'publish_date' => 'published_at',
            'newest' => 'created_at',
        ];
        if (in_array($sort , array_keys($available_sort))) {
            $sort = $available_sort[$sort];
        } else {
            $sort = 'views';
        }

        $order_by = $request->input('order_by');
        if ($order_by !== 'asc' && $order_by !== 'desc') {
            $order_by = 'asc';
        }

        $difficulty = $request->input('difficulty');
        if (intval($difficulty) > 4) {
            $difficulty = 0;
        }

        $musics = Music::query();

        if (strlen(trim($search_text))) {
            $musics = $musics->where(function($query) use ($search_text){
                $query->where('name', 'LIKE', "%{$search_text}%")->
                orWhere('persian_name', 'LIKE', "%{$search_text}%")->
                orWhere('id', $search_text);
            });
        }
        if ($difficulty > 0) {
            $musics = $musics->where('degree', $difficulty);
        }

        $musics = $musics->orderBy($sort , $order_by)->where("status", 1)->paginate(24);

        $list = (new MusicHelper())->prepareMusicsTemplate($musics);
        $last_page = $musics->lastPage();
        $total = $musics->total();

        return response()->json([
            'data' => [
                'data' => $list,
                'last_page' => $last_page,
                'total' => $total,
            ],
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getNMusicList(Request $request)
    {
        $views = View::selectRaw('viewable_id , COUNT(*) AS cnt')->where('viewable_type',Music::class)->where('created_at', '>' , Carbon::now()->subWeek()->format("Y-m-d H:i:s"))->groupBy("viewable_id")->orderBy("cnt","desc")->limit(24)->get();
        $most_viewed_ids = array_column($views->toArray(),'viewable_id');
        if (!empty($most_viewed_ids)) {
            $musics = Music::whereIn('id' , $most_viewed_ids)->orderByRaw('FIELD(id, '.implode(',' , $most_viewed_ids).')')->where("status", 1)->get();
        } else {
            $musics = Music::where('status' , 1)->orderBy('views', 'desc')->limit(24)->get();
        }

        $list = (new MusicHelper())->prepareMusicsTemplate($musics);

        return response()->json([
            'data' => $list,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getLastMusicList(Request $request)
    {
        $musics = Music::orderBy('published_at', 'DESC')->where("status", 1)->
            where('name', 'LIKE', "%{$request->search_text}%")->
            orWhere('persian_name', 'LIKE', "%{$request->search_text}%")->
            orWhere('id', $request->search_text)
        ->paginate(24);

        $list = (new MusicHelper())->prepareMusicsTemplate($musics);
        $last_page = $musics->lastPage();
        $total = $musics->total();

        return response()->json([
            'data' => [
                'data' => $list,
                'last_page' => $last_page,
                'total' => $total,
            ],
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getNLastMusicList(Request $request)
    {
        $musics = Music::orderBy('published_at', 'DESC')->where("status", 1)->take(20)->get();

        $list = (new MusicHelper())->prepareMusicsTemplate($musics);

        return response()->json([
            'data' => $list,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getMusicCompleteInfo(Request $request)
    {
        $messages = array(
            'id.required' => 'id نمی تواند خالی باشد',
            'id.numeric' => 'id باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن اطلاعات آهنگ شکست خورد",
            ], 400);
        }

        $music = Music::where('id', $request->id)->first();
        if (!$music) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "گرفتن اطلاعات آهنگ شکست خورد",
            ], 400);
        }

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $texts = [];
        if ((new UserHelper())->isUserSubscriptionValid($request->header("ApiToken"))) {
            $texts = Text::where('id_music', '=', $request->id)->orderBy("id")->get();
        }

        $data = (new MusicHelper())->musicTemplate($music, $user->id);
        $data['texts'] = $texts;

        return response()->json([
            'data' => $data,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getMusicWithHardest(Request $request)
    {
        $search_key = $request->search_text;
        $hard = $request->hard;
        $musics = Music::orderBy('id', "DESC")->where('degree', $hard)->where("status", 1);
        $musics = $musics->where(function ($query) use ($search_key) {
            $query->where('name', 'like', '%' . $search_key . '%')
                ->orWhere('persian_name', 'like', '%' . $search_key . '%');
        })->paginate(24);

        $list = (new MusicHelper())->prepareMusicsTemplate($musics);
        $last_page = $musics->lastPage();
        $total = $musics->total();

        return response()->json([
            'data' => [
                'data' => $list,
                'last_page' => $last_page,
                'total' => $total,
            ],
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getMusicWithTextPaginate(Request $request)
    {
        $word = $request->word;
        $musics = Music::where("status", 1)->with(['text' => function ($query) use ($word) {
            $query->where('text_english', 'LIKE', "%{$word}%");
        },
        ])->whereHas('text', function ($query) use ($word) {
            $query->where('text_english', 'LIKE', "%{$word}%");
        })->paginate(24);

        $list = [];
        foreach ($musics as $music) {
            $data = (new MusicHelper())->musicTemplate($music);
            $data['text'] = $music['text'];
            $list[] = $data;
        }
        $last_page = $musics->lastPage();
        $total = $musics->total();

        return response()->json([
            'data' => [
                'data' => $list,
                'last_page' => $last_page,
                'total' => $total,
            ],
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function addMusicViewOne(Request $request)
    {
        $messages = array(
            'id.required' => 'id نمی تواند خالی باشد',
            'id.numeric' => 'id باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن ویو شکست خورد",
            ], 400);
        }

        $music = Music::where('id', $request->id)->first();
        if (!$music) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "گرفتن اطلاعات آهنگ شکست خورد",
            ], 400);
        }

        $music->views = $music->views + 1;
        $music->save();

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $music->views()->create([
            'user_id' => $user->id,
        ]);

        return response()->json([
            'data' => null,
            'errors' => [],
            'message' => "ویو با موفقیت اضافه شد",
        ]);
    }

    public function getSingerMusics(Request $request)
    {
        $messages = array(
            'id_singer.required' => 'id_singer نمی تواند خالی باشد',
            'id_singer.numeric' => 'id_singer باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id_singer' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن لیست آهنگ های خواننده شکست خورد",
            ], 400);
        }

        $singer = Singer::where('id', $request->id_singer)->first();
        if (!$singer) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "خواننده یافت نشد.",
            ], 400);
        }

        $musics = $singer->musics()->where("status", 1)->orderBy('views', 'DESC')->paginate(24);
        $list = (new MusicHelper())->prepareMusicsTemplate($musics);
        $last_page = $musics->lastPage();
        $total = $musics->total();

        return response()->json([
            'data' => [
                'data' => $list,
                'last_page' => $last_page,
                'total' => $total,
            ],
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getAlbumMusics(Request $request)
    {
        $messages = array(
            'id_album.required' => 'id_album نمی تواند خالی باشد',
            'id_album.numeric' => 'id_album باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id_album' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن لیست آهنگ های آلبوم شکست خورد",
            ], 400);
        }

        $musics = Music::where('album_id', $request->id_album)->where("status", 1)->get();

        $list = (new MusicHelper())->prepareMusicsTemplate($musics);

        return response()->json([
            'data' => $list,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getSingerMusicsNoPaging(Request $request)
    {
        $messages = array(
            'id_singer.required' => 'id_singer نمی تواند خالی باشد',
            'id_singer.numeric' => 'id_singer باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id_singer' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن لیست آهنگ های خواننده شکست خورد",
            ], 400);
        }

        $singer = Singer::where('id', $request->id_singer)->first();
        if (!$singer) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "خواننده یافت نشد.",
            ], 400);
        }

        $limit = $request->input('limit', 200);
        $musics = $singer->musics()->where("status", 1)->orderBy('views', 'DESC')->limit($limit)->get();
        $list = (new MusicHelper())->prepareMusicsTemplate($musics);

        return response()->json([
            'data' => $list,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }
}

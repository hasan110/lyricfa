<?php

namespace App\Http\Controllers;

use App\Models\Music;
use App\Models\Singer;
use App\Models\Text;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MusicController extends Controller
{
    public function getNRequestedMusic(Request $request)
    {
        $musics = Music::where('is_user_request', 1)->take(20)->inRandomOrder()->get();
        $arr = [];
        foreach ($musics as $music) {

            $singer = SingerController::getSingerById($music->id);
            $num_like = LikeMusicController::getNumberMusicLike($music->id);
            $num_comment = CommentMusicController::getNumberMusicComment($music->id);

            $average_score = ScoreMusicController::getAverageMusicScore($music->id);

            $data = [
                'music' => $music,
                'singers' => $singer,
                'num_like' => $num_like,
                'num_comment' => $num_comment,
                'user_like_it' => 0,
                'average_score' => $average_score
            ];

            $arr[] = $data;
        }

        $response = [
            'data' => $arr,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response);
    }

    public function getRequestedMusic(Request $request)
    {
        $search_key = $request->search_text;
        $musics = Music::orderBy('id', "DESC")->where('is_user_request', 1);
        $musics = $musics->where(function ($query) use ($search_key) {
            $query->where('name', 'like', '%' . $search_key . '%')
                ->orWhere('persian_name', 'like', '%' . $search_key . '%');
        })->paginate(25);

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer = SingerController::getSingerById($music->id);
            $num_like = LikeMusicController::getNumberMusicLike($music->id);
            $num_comment = CommentMusicController::getNumberMusicComment($music->id);
            $average_score = ScoreMusicController::getAverageMusicScore($music->id);
            $music->average_score = $average_score;
            $music->singers = $singer;
            $music->num_like = $num_like;
            $music->num_comment = $num_comment;

            unset($music['id'], $music['name'], $music['persian_name'], $music['album'],
                $music['degree'], $music['size'], $music['interest'],
                $music['counter'], $music['mvideo'], $music['synchvideo'], $music['views'],
                $music['type_video'], $music['start_demo'], $music['end_demo']);
        }

        $response = [
            'data' => $musics,
            'errors' => [
            ],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);
    }

    public function getMusicList(Request $request)
    {

        $musics = Music::orderBy('views', 'DESC')->
        where('name', 'LIKE', "%{$request->search_text}%")->
        orWhere('persian_name', 'LIKE', "%{$request->search_text}%")->
            orWhere('id', $request->search_text)->
        paginate(25);

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer = SingerController::getSingerById($music->id);
            $num_like = LikeMusicController::getNumberMusicLike($music->id);
            $num_comment = CommentMusicController::getNumberMusicComment($music->id);
            $average_score = ScoreMusicController::getAverageMusicScore($music->id);
            $music->average_score = $average_score;
            $music->singers = $singer;
            $music->num_like = $num_like;
            $music->num_comment = $num_comment;

            unset($music['id'], $music['name'], $music['persian_name'], $music['album'],
                $music['degree'], $music['size'], $music['interest'],
                $music['counter'], $music['mvideo'], $music['synchvideo'], $music['views'],
                $music['type_video'], $music['start_demo'], $music['end_demo']);
        }

        $response = [
            'data' => $musics,
            'errors' => [
            ],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);
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

        $musics = $musics->orderBy($sort , $order_by)->paginate(24);

        $list = [];
        $last_page = $musics->lastPage();
        $total = $musics->total();
        foreach ($musics as $music) {
            $singer = SingerController::getSingerById($music->id);
            $num_like = LikeMusicController::getNumberMusicLike($music->id);
            $num_comment = CommentMusicController::getNumberMusicComment($music->id);
            $average_score = ScoreMusicController::getAverageMusicScore($music->id);

            $list[] = [
                'music' => $music,
                'singers' => $singer,
                'num_like' => $num_like,
                'readable_like' => $this->getReadableNumber(intval($num_like)),
                'readable_views' => $this->getReadableNumber(intval($music->views)),
                'num_comment' => $num_comment,
                'user_like_it' => 0,
                'average_score' => +number_format($average_score,1)
            ];
        }

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

    public function getMusicSingersSearchList(Request $request)
    {
        $api_token = $request->header("ApiToken");
        $user = UserController::getUserByToken($api_token);
        $user_id = $user->id;

        $messages = array(
            'search_text.required' => 'متن جستجو الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'search_text' => 'required',
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "جستجو شکست خورد"
            ];
            return response()->json($arr, 400);
        }

        if ($user_id) {
            $musics = Music::orderBy('views', 'DESC')->
            where('name', 'LIKE', "%{$request->search_text}%")->
            orWhere('persian_name', 'LIKE', "%{$request->search_text}%")->
            paginate(25);

            foreach ($musics as $music) {
                $music->music = json_decode(json_encode($music));

                $singer = SingerController::getSingerById($music->id);
                $num_like = LikeMusicController::getNumberMusicLike($music->id);
                $num_comment = CommentMusicController::getNumberMusicComment($music->id);
                $average_score = ScoreMusicController::getAverageMusicScore($music->id);
                $music->average_score = $average_score;
                $music->singers = $singer;
                $music->num_like = $num_like;
                $music->num_comment = $num_comment;

                unset($music['id'], $music['name'], $music['persian_name'], $music['album'],
                    $music['degree'], $music['size'], $music['interest'],
                    $music['counter'], $music['mvideo'], $music['synchvideo'], $music['views'],
                    $music['type_video'], $music['start_demo'], $music['end_demo']);
            }

            $singers = Singer::orderBy('id', 'DESC')->
            where('english_name', 'LIKE', "%{$request->search_text}%")->
            orWhere('persian_name', 'LIKE', "%{$request->search_text}%")->
            paginate(25);

            foreach ($singers as $singer) {
                $singer->singer = json_decode(json_encode($singer));

                $num_like = LikeSingerController::getNumberSingerLike($singer->id);
                $num_comment = CommentSingerController::getNumberSingerComment($singer->id);

                $singer->num_like = $num_like;
                $singer->num_comment = $num_comment;
                $singer->user_like_it = LikeSingerController::isUserLike($singer->id, $user_id);

                unset($singer['id'], $singer['english_name'], $singer['persian_name']);
            }

            $response = [
                'singers' => $singers,
                'musics' => $musics,
                'errors' => [],
                'message' => "اطلاعات با موفقیت گرفته شد",
            ];
            return response()->json($response);
        } else {
            $response = [
                'data' => null,
                'errors' => [
                ],
                'message' => "مشکل در احراز هویت",
            ];
            return response()->json($response, 401);
        }
    }

    public function getNMusicList(Request $request)
    {
        $musics = Music::orderBy('views', 'DESC')->take(20)->get();

        $arr = [];
        foreach ($musics as $music) {
            $singer = SingerController::getSingerById($music->id);
            $num_like = LikeMusicController::getNumberMusicLike($music->id);
            $num_comment = CommentMusicController::getNumberMusicComment($music->id);
            $average_score = ScoreMusicController::getAverageMusicScore($music->id);
            $data = [
                'music' => $music,
                'singers' => $singer,
                'num_like' => $num_like,
                'num_comment' => $num_comment,
                'user_like_it' => 0,
                'average_score' => $average_score
            ];
            $arr[] = $data;
        }

        $response = [
            'data' => $arr,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response);
    }

    public function getListMusicByIds($listMusics)
    {
        $musics = [];
        foreach ($listMusics as $item) {
            $musics[] = Music::where('id', $item)->first();
        }
        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer = SingerController::getSingerById($music->id);
            $num_like = LikeMusicController::getNumberMusicLike($music->id);
            $num_comment = CommentMusicController::getNumberMusicComment($music->id);
            $average_score = ScoreMusicController::getAverageMusicScore($music->id);
            $music->singers = $singer;
            $music->num_like = $num_like;
            $music->num_comment = $num_comment;
            $music->user_like_it = 0;
            $music->average_score = $average_score;
            $music->readable_like = $this->getReadableNumber(intval($num_like));
            $music->readable_views = $this->getReadableNumber(intval($music->views));

            unset($music['id'], $music['name'], $music['persian_name'], $music['album'],
                $music['degree'], $music['size'], $music['interest'],
                $music['counter'], $music['mvideo'], $music['synchvideo'], $music['views'],
                $music['type_video'], $music['start_demo'], $music['end_demo']);
        }

        $response = [
            'data' => $musics,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response);
    }

    public static function getListMusicByIdsOnlyMusics($listMusics)
    {
        $musics = [];
        foreach ($listMusics as $item) {
            $musics[] = Music::where('id', $item)->first();
        }
        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer = SingerController::getSingerById($music->id);
            $num_like = LikeMusicController::getNumberMusicLike($music->id);
            $num_comment = CommentMusicController::getNumberMusicComment($music->id);

            $average_score = ScoreMusicController::getAverageMusicScore($music->id);


            $music->singers = $singer;
            $music->num_like = $num_like;
            $music->num_comment = $num_comment;
            $music->user_like_it = 0;
            $music->average_score = $average_score;

            unset($music['id'], $music['name'], $music['persian_name'], $music['album'],
                $music['degree'], $music['size'], $music['interest'],
                $music['counter'], $music['mvideo'], $music['synchvideo'], $music['views'],
                $music['type_video'], $music['start_demo'], $music['end_demo']);
        }

        $response = [
            'data' => $musics,
            'errors' => [
            ],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return $musics;
    }

    public static function getAllMusicWithPlayList($listMusics, $search_text)
    {
        $musics = Music::orderBy('id', 'ASC')->where('name', 'LIKE', "%{$search_text}%")->orWhere('persian_name', 'LIKE', "%{$search_text}%")->paginate(25);

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer = SingerController::getSingerById($music->id);
            $num_like = LikeMusicController::getNumberMusicLike($music->id);
            $num_comment = CommentMusicController::getNumberMusicComment($music->id);

            $average_score = ScoreMusicController::getAverageMusicScore($music->id);

            $music->singers = $singer;
            $music->num_like = $num_like;
            $music->num_comment = $num_comment;
            $music->user_like_it = 0;
            $music->average_score = $average_score;
            if (in_array($music->id, $listMusics)) {
                $music->in_playlist = 1;
            } else {
                $music->in_playlist = 0;
            }

            unset($music['id'], $music['name'], $music['persian_name'], $music['album'],
                $music['degree'], $music['size'], $music['interest'],
                $music['counter'], $music['mvideo'], $music['synchvideo'], $music['views'],
                $music['type_video'], $music['start_demo'], $music['end_demo']);
        }

        $response = [
            'data' => $musics,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response);
    }

    public function getLastMusicList(Request $request)
    {
        $musics = Music::orderBy('published_at', 'DESC')->
        where('name', 'LIKE', "%{$request->search_text}%")->
        orWhere('persian_name', 'LIKE', "%{$request->search_text}%")->
            orWhere('id', $request->search_text)->paginate(25);

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));
            $singer = SingerController::getSingerById($music->id);
            $num_like = LikeMusicController::getNumberMusicLike($music->id);
            $num_comment = CommentMusicController::getNumberMusicComment($music->id);
            $average_score = ScoreMusicController::getAverageMusicScore($music->id);
            $music->singers = $singer;
            $music->num_like = $num_like;
            $music->num_comment = $num_comment;
            $music->user_like_it = 0;
            $music->average_score = $average_score;

            unset($music['id'], $music['name'], $music['persian_name'], $music['album'],
                $music['degree'], $music['size'], $music['interest'],
                $music['counter'], $music['mvideo'], $music['synchvideo'], $music['views'],
                $music['type_video'], $music['start_demo'], $music['end_demo']);
        }

        $response = [
            'data' => $musics,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response);
    }

    public function getNLastMusicList(Request $request)
    {
        $musics = Music::orderBy('published_at', 'DESC')->take(20)->get();
        $arr = [];
        foreach ($musics as $music) {
            $singer = SingerController::getSingerById($music->id);
            $num_like = LikeMusicController::getNumberMusicLike($music->id);
            $num_comment = CommentMusicController::getNumberMusicComment($music->id);
            $average_score = ScoreMusicController::getAverageMusicScore($music->id);
            $data = [
                'music' => $music,
                'singers' => $singer,
                'num_like' => $num_like,
                'num_comment' => $num_comment,
                'user_like_it' => 0,
                'average_score' => $average_score
            ];
            $arr[] = $data;
        }

        $response = [
            'data' => $arr,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response);
    }

    public static function getMusicById($id)
    {
        return Music::where('id', $id)->first();
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
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن اطلاعات آهنگ شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $id = $request->id;
        $music = $this->getMusicById($id);

        $singer = SingerController::getSingerById($music->id);

        $api_token = $request->header("ApiToken");

        $user_id = UserController::getUserByToken($api_token)->id;

        $num_like = LikeMusicController::getNumberMusicLike($id);

        $num_comment = CommentMusicController::getNumberMusicComment($id);

        $user_like_it = LikeMusicController::isUserLike($id, $user_id);

        $average_score = ScoreMusicController::getAverageMusicScore($id);

        $texts = [];
        if (UserController::isUserSubscriptionValid($request)) {
            $texts = Text::where('id_music', '=', $id)->orderBy("id")->get();
        }

        $data = [
            'music' => $music,
            'texts' => $texts,
            'singers' => $singer,
            'num_like' => $num_like,
            'readable_like' => $this->getReadableNumber(intval($num_like)),
            'num_comment' => $num_comment,
            'readable_comment' => $this->getReadableNumber(intval($num_comment)),
            'user_like_it' => $user_like_it,
            'average_score' => +number_format($average_score,1)
        ];

        $arr = [
            'data' => $data,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];

        return response()->json($arr, 200);

    }

    public function getMusicWithHardest(Request $request)
    {
        $search_key = $request->search_text;
        $hard = $request->hard;
        $musics = Music::orderBy('id', "DESC")->where('degree', $hard);
        $musics = $musics->where(function ($query) use ($search_key) {
            $query->where('name', 'like', '%' . $search_key . '%')
                ->orWhere('persian_name', 'like', '%' . $search_key . '%');
        })->paginate(25);

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));
            $singer = SingerController::getSingerById($music->id);
            $num_like = LikeMusicController::getNumberMusicLike($music->id);
            $num_comment = CommentMusicController::getNumberMusicComment($music->id);
            $average_score = ScoreMusicController::getAverageMusicScore($music->id);
            $music->average_score = $average_score;
            $music->singers = $singer;
            $music->num_like = $num_like;
            $music->num_comment = $num_comment;

            unset($music['id'], $music['name'], $music['persian_name'], $music['album'],
                $music['degree'], $music['size'], $music['interest'],
                $music['counter'], $music['mvideo'], $music['synchvideo'], $music['views'],
                $music['type_video'], $music['start_demo'], $music['end_demo']);
        }

        $response = [
            'data' => $musics,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response);
    }

    public function getMusicWithTextPaginate(Request $request)
    {
        $word = $request->word;
        $musics = Music::with(['text' => function ($query) use ($word) {
            $query->where('text_english', 'LIKE', "%{$word}%");
        },
        ])->whereHas('text', function ($query) use ($word) {
            $query->where('text_english', 'LIKE', "%{$word}%");
        })->paginate(25);

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer = SingerController::getSingerById($music->id);
            $num_like = LikeMusicController::getNumberMusicLike($music->id);
            $num_comment = CommentMusicController::getNumberMusicComment($music->id);
            $average_score = ScoreMusicController::getAverageMusicScore($music->id);
            $music->average_score = $average_score;
            $music->singers = $singer;
            $music->num_like = $num_like;
            $music->num_comment = $num_comment;
            $music->readable_like = $this->getReadableNumber(intval($num_like));
            $music->readable_views = $this->getReadableNumber(intval($music->views));

            unset($music['id'], $music['name'], $music['persian_name'], $music['album'],
                $music['degree'], $music['size'], $music['interest'],
                $music['counter'], $music['mvideo'], $music['synchvideo'], $music['views'],
                $music['type_video'], $music['start_demo'], $music['end_demo'], $music['text']);
        }

        $response = [
            'data' => $musics,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response);
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
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن ویو شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $music = $this->getMusicById($request->id);
        $music->views = $music->views + 1;
        $music->save();

        $user_id = UserController::getUserByToken($request->header("ApiToken"))->id;
        $music->views()->create([
            'user_id' => $user_id,
        ]);

        $response = [
            'data' => null,
            'errors' => [],
            'message' => "ویو با موفقیت اضافه شد",
        ];
        return response()->json($response);
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
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن لیست آهنگ های خواننده شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $singer = Singer::where('id', $request->id_singer)->first();
        if (!$singer) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "خواننده یافت نشد.",
            ], 400);
        }
        $musics = $singer->musics()->orderBy('views', 'DESC')->paginate(25);

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer = SingerController::getSingerById($music->id);
            $num_like = LikeMusicController::getNumberMusicLike($music->id);
            $num_comment = CommentMusicController::getNumberMusicComment($music->id);
            $average_score = ScoreMusicController::getAverageMusicScore($music->id);
            $music->average_score = $average_score;
            $music->singers = $singer;
            $music->num_like = $num_like;
            $music->num_comment = $num_comment;
            $music->readable_like = $this->getReadableNumber(intval($num_like));
            $music->readable_views = $this->getReadableNumber(intval($music->views));
            $music->readable_comment = $this->getReadableNumber(intval($num_comment));

            unset($music['id'], $music['name'], $music['persian_name'], $music['album'],
                $music['degree'], $music['size'], $music['interest'],
                $music['counter'], $music['mvideo'], $music['synchvideo'], $music['views'],
                $music['type_video'], $music['start_demo'], $music['end_demo']);
        }

        $response = [
            'data' => $musics,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response);
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
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن لیست آهنگ های آلبوم شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $musics = Music::where('album_id', $request->id_album)->get();

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer = SingerController::getSingerById($music->id);
            $num_like = LikeMusicController::getNumberMusicLike($music->id);
            $num_comment = CommentMusicController::getNumberMusicComment($music->id);
            $average_score = ScoreMusicController::getAverageMusicScore($music->id);
            $music->average_score = $average_score;
            $music->singers = $singer;
            $music->num_like = $num_like;
            $music->num_comment = $num_comment;
            $music->readable_like = $this->getReadableNumber(intval($num_like));
            $music->readable_views = $this->getReadableNumber(intval($music->views));
            $music->readable_comment = $this->getReadableNumber(intval($num_comment));

            unset($music['id'], $music['name'], $music['persian_name'], $music['album'],
                $music['degree'], $music['size'], $music['interest'],
                $music['counter'], $music['mvideo'], $music['synchvideo'], $music['views'],
                $music['type_video'], $music['start_demo'], $music['end_demo']);
        }

        $response = [
            'data' => $musics,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response);
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
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن لیست آهنگ های خواننده شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $singer = Singer::where('id', $request->id_singer)->first();
        if (!$singer) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "خواننده یافت نشد.",
            ], 400);
        }
        $musics = $singer->musics()->orderBy('views', 'DESC')->get();

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer = SingerController::getSingerById($music->id);
            $num_like = LikeMusicController::getNumberMusicLike($music->id);
            $num_comment = CommentMusicController::getNumberMusicComment($music->id);
            $average_score = ScoreMusicController::getAverageMusicScore($music->id);
            $music->average_score = +number_format($average_score,1);
            $music->singers = $singer;
            $music->num_like = $num_like;
            $music->readable_like = $this->getReadableNumber(intval($num_like));
            $music->num_comment = $num_comment;
            $music->readable_comment = $this->getReadableNumber(intval($num_comment));

            unset($music['id'], $music['name'], $music['persian_name'], $music['album'],
                $music['degree'], $music['size'], $music['interest'],
                $music['counter'], $music['mvideo'], $music['synchvideo'], $music['views'],
                $music['type_video'], $music['start_demo'], $music['end_demo']);
        }

        $response = [
            'data' => $musics,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Music;
use App\Models\Singer;
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

            $singer_id = $music->singers;

            $singer = SingerController::getSingerById($singer_id);
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
            'errors' => [
                // 'username'=>'required',
                // 'username'=>'required',
                // 'username'=>'required',
                // 'username'=>'required',
            ],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);
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

            $singer_id = $music->singers;

            $singer = SingerController::getSingerById($singer_id);
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

            $singer_id = $music->singers;

            $singer = SingerController::getSingerById($singer_id);
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

    public function getMusicSingersSearchList(Request $request)
    {


        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        $user_id = $user->id;

        $messsages = array(
            'search_text.required' => 'متن جستجو الزامی است'

        );

        $validator = Validator::make($request->all(), [
            'search_text' => 'required',
        ], $messsages);


        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "جستجو شکست خورد"
            ];
            return response()->json($arr, 400);
        }


        if ($user_id) {
            //region music
            $musics = Music::orderBy('views', 'DESC')->
            where('name', 'LIKE', "%{$request->search_text}%")->
            orWhere('persian_name', 'LIKE', "%{$request->search_text}%")->
            paginate(25);

            foreach ($musics as $music) {
                $music->music = json_decode(json_encode($music));

                $singer_id = $music->singers;

                $singer = SingerController::getSingerById($singer_id);
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
            //endregion
            //region singer
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

            //endregion


            $response = [
                'singers' => $singers,
                'musics' => $musics,
                'errors' => [
                ],
                'message' => "اطلاعات با موفقیت گرفته شد",
            ];
            return response()->json($response, 200);
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

    public function getMusicVideoList(Request $request)
    {
        $search_key = $request->search_text;
        $hard = $request->hard;
        $musics = Music::orderBy('id', "DESC")->where('mvideo', "=", 1);
        $musics = $musics->where(function ($query) use ($search_key) {
            $query->where('name', 'like', '%' . $search_key . '%')
                ->orWhere('persian_name', 'like', '%' . $search_key . '%');
        })->paginate(25);

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer_id = $music->singers;

            $singer = SingerController::getSingerById($singer_id);
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

    public function getNMusicList(Request $request)
    {

        $musics = Music::orderBy('views', 'DESC')->take(20)->get();

        $arr = [];
        foreach ($musics as $music) {

            $singer_id = $music->singers;

            $singer = SingerController::getSingerById($singer_id);
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
            'errors' => [
                // 'username'=>'required',
                // 'username'=>'required',
                // 'username'=>'required',
                // 'username'=>'required',
            ],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);
    }

    public static function getListMusicByIds($listMusics)
    {

        $musics = [];

        foreach ($listMusics as $item) {
            $musics[] = Music::where('id', $item)->first();
        }

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer_id = $music->singers;

            $singer = SingerController::getSingerById($singer_id);
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
        return response()->json($response, 200);

    }


    public static function getListMusicByIdsOnlyMusics($listMusics)
    {

        $musics = [];

        foreach ($listMusics as $item) {
            $musics[] = Music::where('id', $item)->first();
        }

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer_id = $music->singers;

            $singer = SingerController::getSingerById($singer_id);
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

            $singer_id = $music->singers;

            $singer = SingerController::getSingerById($singer_id);
            $num_like = LikeMusicController::getNumberMusicLike($music->id);
            $num_comment = CommentMusicController::getNumberMusicComment($music->id);

            $average_score = ScoreMusicController::getAverageMusicScore($music->id);

            $music->singers = $singer;
            $music->num_like = $num_like;
            $music->num_comment = $num_comment;
            $music->user_like_it = 0;
            $music->average_score = $average_score;
            // return  ((string) $music->id);
            // if($listMusics->contains($music->id)){
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
            'errors' => [
            ],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);
    }

    public function getLastMusicList(Request $request)
    {
        $musics = Music::orderBy('publicated_at', 'DESC')->
        where('name', 'LIKE', "%{$request->search_text}%")->
        orWhere('persian_name', 'LIKE', "%{$request->search_text}%")->
            orWhere('id', $request->search_text)->paginate(25);

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer_id = $music->singers;

            $singer = SingerController::getSingerById($singer_id);
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
        return response()->json($response, 200);
    }

    public function getNLastMusicList(Request $request)
    {

        //  $musics = DB::table('musics') ->paginate(25);
        $musics = Music::orderBy('publicated_at', 'DESC')->take(20)->get();
        $arr = [];
        foreach ($musics as $music) {

            $singer_id = $music->singers;

            $singer = SingerController::getSingerById($singer_id);
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
            'errors' => [
                // 'username'=>'required',
                // 'username'=>'required',
                // 'username'=>'required',
                // 'username'=>'required',
            ],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);
    }

    public static function getMusicById($id)
    {

        $get_music = Music::where('id', $id)->first();

        return $get_music;
    }

    public function getMusicCompleteInfo(Request $request)
    {


        $messsages = array(
            'id.required' => 'id نمی تواند خالی باشد',
            'id.numeric' => 'id باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
        ], $messsages);

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
        $singer_id = $music->singers;

        $singer = SingerController::getSingerById($singer_id);

        $api_token = $request->header("ApiToken");

        $user_id = UserController::getUserByToken($api_token)->id;

        $num_like = LikeMusicController::getNumberMusicLike($id);

        $num_comment = CommentMusicController::getNumberMusicComment($id);

        $user_like_it = LikeMusicController::isUserLike($id, $user_id);

        $average_score = ScoreMusicController::getAverageMusicScore($id);

        $data = [
            'music' => $music,
            'singers' => $singer,
            'num_like' => $num_like,
            'num_comment' => $num_comment,
            'user_like_it' => $user_like_it,
            'average_score' => $average_score
        ];

        $arr = [
            'data' => $data,
            'errors' => [
            ],
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
        // dd($musics);

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer_id = $music->singers;

            $singer = SingerController::getSingerById($singer_id);
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

    public function getMusicWithTextPaginate(Request $request)
    {

        $word = $request->word;
        $musics = Music::with(['text' => function ($query) use ($word) {
            $query->where('text_english', 'LIKE', "%{$word}%");
        },
        ])->whereHas('text', function ($query) use ($word) {

            $query->where('text_english', 'LIKE', "%{$word}%");

        })
            ->paginate(25);

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer_id = $music->singers;

            $singer = SingerController::getSingerById($singer_id);
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
                $music['type_video'], $music['start_demo'], $music['end_demo'], $music['text']);
        }

        $response = [
            'data' => $musics,
            'errors' => [
            ],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);
    }

    public function addMusicViewOne(Request $request)
    {
        $messsages = array(
            'id.required' => 'id نمی تواند خالی باشد',
            'id.numeric' => 'id باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
        ], $messsages);

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

        $response = [
            'data' => null,
            'errors' => [
            ],
            'message' => "ویو با موفقیت اضافه شد",
        ];
        return response()->json($response, 200);
    }

    public function getSingerMusics(Request $request)
    {
        $messsages = array(
            'id_singer.required' => 'id_singer نمی تواند خالی باشد',
            'id_singer.numeric' => 'id_singer باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id_singer' => 'required|numeric',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن لیست آهنگ های خواننده شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $singer_4digit = $this->set4digit($request->id_singer);
        $musics = Music::orderBy('views', 'DESC')->where('singers', 'LIKE', "%{$singer_4digit}%")->paginate(25);

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer_id = $music->singers;

            $singer = SingerController::getSingerById($singer_id);
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

    public function getAlbumMusics(Request $request)
    {
        $messsages = array(
            'id_album.required' => 'id_album نمی تواند خالی باشد',
            'id_album.numeric' => 'id_album باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id_album' => 'required|numeric',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن لیست آهنگ های آلبوم شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $musics = Music::where('album', $request->id_album)->get();

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer_id = $music->singers;

            $singer = SingerController::getSingerById($singer_id);
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

    public function getSumMusics(Request $request)
    {
        $messsages = array(
            'id_sum.required' => 'id_sum نمی تواند خالی باشد',
            'id_sum.numeric' => 'id_sum باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id_sum' => 'required|numeric',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن لیست آهنگ ها شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $sum_4digit = $this->set4digit($request->id_sum);
        $musics = Music::orderBy('views', 'DESC')->where('cat_musics', 'LIKE', "%{$sum_4digit}%")->get();

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer_id = $music->singers;

            $singer = SingerController::getSingerById($singer_id);
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

    public function getSingerMusicsNoPaging(Request $request)
    {
        $messsages = array(
            'id_singer.required' => 'id_singer نمی تواند خالی باشد',
            'id_singer.numeric' => 'id_singer باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id_singer' => 'required|numeric',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن لیست آهنگ های خواننده شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $singer_4digit = $this->set4digit($request->id_singer);
        $musics = Music::orderBy('views', 'DESC')->where('singers', 'LIKE', "%{$singer_4digit}%")->get();

        foreach ($musics as $music) {
            $music->music = json_decode(json_encode($music));

            $singer_id = $music->singers;

            $singer = SingerController::getSingerById($singer_id);
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

    private function set4digit(string $mainString)
    {
        $length = Str::length($mainString);

        switch ($length) {
            case 1:
                return '000' . $mainString;
                break;
            case 2:
                return '00' . $mainString;
                break;
            case 3:
                return '0' . $mainString;
                break;
            case 4:
                return $mainString;
                break;
            default:
                return '0000';
                break;
        }
    }



}

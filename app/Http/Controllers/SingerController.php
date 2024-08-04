<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Music;
use App\Models\Singer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SingerController extends Controller
{
    public static function getSingerById($music_id)
    {
        $music = Music::find($music_id);
        if (!$music) {
            return [];
        }

        return $music->singers;
    }

    public static function getSingerByAlbumId($album_id)
    {
        $album = Album::find($album_id);
        if (!$album) {
            return [];
        }

        return $album->singers;
    }

    public function getSingersList(Request $request)
    {
        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        $user_id  = $user->id;
        if ($user_id) {
            $singers = Singer::orderBy('english_name', 'ASC')->
            where('english_name', 'LIKE', "%{$request->search_text}%")->
            orWhere('persian_name', 'LIKE', "%{$request->search_text}%")->
            paginate(25);

            foreach ($singers as $singer) {
                $singer->singer = json_decode(json_encode($singer));

                $num_like = LikeSingerController::getNumberSingerLike($singer->id);
                $num_comment = CommentSingerController::getNumberSingerComment($singer->id);

                $singer->num_like = $num_like;
                $singer->readable_like = $this->getReadableNumber(intval($num_like));
                $singer->num_comment = $num_comment;
                $singer->user_like_it = LikeSingerController::isUserLike($singer->id,$user_id);

            unset($singer['id'], $singer['english_name'], $singer['persian_name']);
        }

            $response = [
                'data' => $singers,
                'errors' => [
                ],
                'message' => "اطلاعات با موفقیت گرفته شد",
            ];
            return response()->json($response, 200);
        }else{
            $response = [
                'data' => null,
                'errors' => [
                ],
                'message' => "مشکل در احراز هویت",
            ];
            return response()->json($response, 401);
        }
    }

    public function getNSingerList(Request $request)
    {
        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        $user_id  = $user->id;
        if($user_id) {
            $singers = Singer::take(20)->inRandomOrder()->get();
            $arr = [];
            foreach ($singers as $singer) {

                $num_like = LikeSingerController::getNumberSingerLike($singer->id);
                $num_comment = CommentSingerController::getNumberSingerComment($singer->id);

                $data = [
                    'singer' => $singer,
                    'num_like' => $num_like,
                    'num_comment' => $num_comment,
                    'user_like_it' =>  LikeSingerController::isUserLike($singer->id, $user_id)
            ];

            $arr[] = $data;
        }

            $response = [
                'data' => $arr,
                'errors' => [
                ],
                'message' => "اطلاعات با موفقیت گرفته شد",
            ];
            return response()->json($response, 200);
        }else{
            $response = [
                'data' => null,
                'errors' => [
                ],
                'message' => "مشکل در احراز هویت",
            ];
            return response()->json($response, 200);
        }
    }

    public function getSingerByIdWithLike(Request $request)
    {
        $api_token = $request->header("ApiToken");
        $user = UserController::getUserByToken($api_token);
        $user_id = $user->id;

        $messages = array(
            'singer_id.required' => 'شناسه ی خواننده الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'singer_id' => 'required',
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن لایک شکست خورد"
            ];
            return response()->json($arr, 400);
        }

        if ($user_id) {

            $id = $request->singer_id;
            $singer = Singer::where('id', $id)->first();
            $num_like = LikeSingerController::getNumberSingerLike($id);
            $num_comment = CommentSingerController::getNumberSingerComment($id);

            $user_like_it = LikeSingerController::isUserLike($id, $user_id);

            $data = [
                'singer' => $singer,
                'num_like' => $num_like,
                'num_comment' => $num_comment,
                'readable_like' => $this->getReadableNumber(intval($num_like)),
                'readable_comment' => $this->getReadableNumber(intval($num_comment)),
                'user_like_it' => $user_like_it
            ];

            $response = [
                'data' => $data,
                'errors' => [],
                'message' => "اطلاعات با موفقیت گرفته شد",
            ];
            return response()->json($response);

        } else {
            $response = [
                'data' => null,
                'errors' => [],
                'message' => "کاربر احراز هویت نشد",
            ];
            return response()->json($response, 401);
        }
    }
}

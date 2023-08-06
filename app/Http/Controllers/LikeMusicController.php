<?php

namespace App\Http\Controllers;

use App\Models\LikeMusic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Type\Integer;

class LikeMusicController extends Controller
{

    public static function getNumberMusicLike($id_music)
    {
        return LikeMusic::where('id_song', $id_music)->count();
    }

    public static function isUserLike($id_music, $id_user)
    {
        return LikeMusic::where('id_song', $id_music)->where('id_user', $id_user)->count();
    }

    public function addMusicLike(Request $request)
    {

        $messsages = array(
            'music_id.required' => 'شناسه آهنگ الزامی است',
            'music_id.numeric' => 'شناسه آهنگ باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'music_id' => 'required|numeric'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  لایک کردن شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $name = $this->likeIsExistInListLikes($request);

        if (isset($name)) { // array use count
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => " این آهنگ توسط این کاربر قبلا لایک شده است",
            ];

            return response()->json($arr, 400);
        }


        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;

            $like = new LikeMusic();
            $like->id_song = (int)$request->music_id;
            $like->id_user = $user_id;
            $like->save();

            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "لایک کردن موفقیت آمیز بود",
            ];

            return response()->json($arr, 200);
        } else {
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "کاربر احراز هویت نشده است",
            ];

            return response()->json($arr, 401);
        }
    }

    public function removeMusicLike(Request $request)
    {

        $messsages = array(
            'music_id.required' => 'شناسه آهنگ الزامی است',
            'music_id.numeric' => 'شناسه آهنگ باید شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'music_id' => 'required|numeric',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  لایک کردن شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $name = $this->likeIsExistInListLikes($request);

        if (!isset($name)) { // array use count
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => " این آهنگ توسط این کاربر لایک نشده است",
            ];

            return response()->json($arr, 400);
        }

        $like = $name;
        $like->delete();

        $arr = [
            'data' => null,
            'errors' => null,
            'message' => "حذف لایک موفقیت آمیز بود",
        ];

        return response()->json($arr, 200);

    }

    public function likeIsExistInListLikes(Request $request)
    {

        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;
            return LikeMusic::where('id_song', $request->music_id)->where('id_user', $user_id)->first();
        } else {
            return null;
        }
    }

}

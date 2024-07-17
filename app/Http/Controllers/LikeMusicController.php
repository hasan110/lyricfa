<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\LikeMusic;
use App\Models\Music;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Ramsey\Uuid\Type\Integer;

class LikeMusicController extends Controller
{
    public static function getNumberMusicLike($id_music)
    {
        $music = Music::where('id', $id_music)->first();
        return $music ? $music->likes()->count() : 0;
    }

    public static function isUserLike($id_music, $id_user)
    {
        $music = Music::where('id', $id_music)->first();
        return $music ? $music->likes()->where('user_id', $id_user)->count() : 0;
    }

    public function addMusicLike(Request $request)
    {
        $messages = array(
            'music_id.required' => 'شناسه آهنگ الزامی است',
            'music_id.numeric' => 'شناسه آهنگ باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'music_id' => 'required|numeric'
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  لایک کردن شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $name = $this->likeIsExistInListLikes($request);

        if (isset($name)) {
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
            $music = Music::where('id', $request->music_id)->first();
            if (!$music) {
                $arr = [
                    'data' => null,
                    'errors' => [],
                    'message' => "  موزیک یافت نشد",
                ];
                return response()->json($arr, 400);
            }

            $music->likes()->create([
                'user_id' => $user_id
            ]);

            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "لایک کردن موفقیت آمیز بود",
            ];
            return response()->json($arr);
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
        $messages = array(
            'music_id.required' => 'شناسه آهنگ الزامی است',
            'music_id.numeric' => 'شناسه آهنگ باید شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'music_id' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  لایک کردن شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $like = $this->likeIsExistInListLikes($request);

        if (!isset($like)) {
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => " این آهنگ توسط این کاربر لایک نشده است",
            ];
            return response()->json($arr, 400);
        }

        $like->delete();

        $arr = [
            'data' => null,
            'errors' => null,
            'message' => "حذف لایک موفقیت آمیز بود",
        ];
        return response()->json($arr);
    }

    public function likeIsExistInListLikes(Request $request)
    {
        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;
            $music = Music::where('id', $request->music_id)->first();
            if (!$music) {
                return null;
            }
            return $music->likes()->where('user_id', $user_id)->first();
        } else {
            return null;
        }
    }

}

<?php

namespace App\Http\Controllers;

use App\Models\LikeSinger;
use App\Models\Singer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class LikeSingerController extends Controller
{

    public static function getNumberSingerLike($id_singer)
    {
        $singer = Singer::where('id', $id_singer)->first();
        return $singer ? $singer->likes()->count() : 0;
    }

    public static function isUserLike($id_singer, $id_user)
    {
        $singer = Singer::where('id', $id_singer)->first();
        return $singer ? $singer->likes()->where('user_id', $id_user)->count() : 0;
    }

    public function addSingerLike(Request $request)
    {
        $messages = array(
            'singer_id.required' => 'شناسه خواننده الزامی است',
            'singer_id.numeric' => 'شناسه خواننده باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'singer_id' => 'required|numeric'
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
                'message' => " این خواننده توسط این کاربر قبلا لایک شده است",
            ];
            return response()->json($arr, 400);
        }

        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;
            $singer = Singer::where('id', $request->singer_id)->first();
            if (!$singer) {
                $arr = [
                    'data' => null,
                    'errors' => [],
                    'message' => "خواننده یافت نشد",
                ];
                return response()->json($arr, 400);
            }

            $singer->likes()->create([
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

    public function removeSingerLike(Request $request)
    {
        $messages = array(
            'singer_id.required' => 'شناسه خواننده الزامی است',
            'singer_id.numeric' => 'شناسه خواننده باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'singer_id' => 'required|numeric'
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
                'message' => " این خواننده توسط این کاربر لایک نشده است",
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
            $singer = Singer::where('id', $request->singer_id)->first();
            if (!$singer) {
                return null;
            }
            return $singer->likes()->where('user_id', $user_id)->first();
        } else {
            return null;
        }
    }

}

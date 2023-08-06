<?php

namespace App\Http\Controllers;

use App\Models\LikeSinger;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class LikeSingerController extends Controller
{

    public static function getNumberSingerLike($id_singer)
    {
        return LikeSinger::where('id_singer', $id_singer)->count();
    }

    public static function isUserLike($id_singer, $id_user)
    {
        return LikeSinger::where('id_singer', $id_singer)->where('id_user', $id_user)->count();
    }

    public function addSingerLike(Request $request)
    {

        $messsages = array(
            'singer_id.required' => 'شناسه خواننده الزامی است',
            'singer_id.numeric' => 'شناسه خواننده باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'singer_id' => 'required|numeric'
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
                'message' => " این خواننده توسط این کاربر قبلا لایک شده است",
            ];

            return response()->json($arr, 400);
        }


        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;
            $like = new LikeSinger();
            $like->id_singer = (int)$request->singer_id;
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

    public function removeSingerLike(Request $request)
    {

        $messsages = array(
            'singer_id.required' => 'شناسه خواننده الزامی است',
            'singer_id.numeric' => 'شناسه خواننده باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'singer_id' => 'required|numeric'
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
                'message' => " این خواننده توسط این کاربر لایک نشده است",
            ];

            return response()->json($arr, 400);
        }


        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {

            $like = $name;
            $like->delete();

            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "حذف لایک موفقیت آمیز بود",
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

    public function likeIsExistInListLikes(Request $request)
    {

        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;
            return LikeSinger::where('id_singer', $request->singer_id)->where('id_user', $user_id)->first();

        } else {
            return null;
        }
    }

}

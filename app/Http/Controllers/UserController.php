<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function saveFcmRefreshTokenInServer(Request $request)
    {
        $messages = array(
            'token.required' => 'توکن الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش کاربر شکست خورد"
            ], 400);
        }

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));
        if ($user) {
            $user->update([
                'fcm_refresh_token' => $request->token
            ]);

            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => " ویرایش کاربر با موفقیت انجام شد",
            ]);
        } else {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => " مشکل در احراز هویت کاربر",
            ], 401);
        }
    }

    public function getUser(Request $request)
    {
        $user = (new UserHelper())->getUserDetailByToken($request->header("ApiToken"));
        $user->sliders = Slider::where('status', 1)->orderBy("updated_at", "desc")->get();

        if ($user) {
            return response()->json([
                'data' => $user,
                'errors' => null,
                'message' => "اطلاعات با موفقیت گرفته شد",
            ]);
        } else {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "خطا در گرفتن اطلاعات",
            ], 400);
        }
    }
}

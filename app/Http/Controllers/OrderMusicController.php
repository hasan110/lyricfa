<?php

namespace App\Http\Controllers;

use App\Models\OrderMusic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderMusicController extends Controller
{

    public function addOrderMusic(Request $request)
    {


        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;


            $messsages = array(
                'music_name.required' => 'نام آهنگ الزامی است',
                'singer_name.required' => 'نام خواننده الزامی است'
            );

            $validator = Validator::make($request->all(), [
                'music_name' => 'required',
                'singer_name' => 'required',
            ], $messsages);

            if ($validator->fails()) {
                $arr = [
                    'data' => null,
                    'errors' => $validator->errors(),
                    'message' => "  سفارش آهنگ شکست خورد",
                ];
                return response()->json($arr, 400);
            }


            $order = new OrderMusic();
            $order->music_name = $request->music_name;
            $order->singer_name = $request->singer_name;
            $order->user_id = $user_id;
            $order->save();


            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "سفارش آهنگ شما با موفقیت ثبت شد.",
            ];

            return response()->json($arr, 200);

        } else {
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "خطا در احراز هویت کاربر",
            ];
            return response()->json($arr, 400);
        }

    }

}

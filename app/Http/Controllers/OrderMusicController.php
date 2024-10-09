<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Models\OrderMusic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderMusicController extends Controller
{
    public function addOrderMusic(Request $request)
    {
        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $messages = array(
            'music_name.required' => 'نام آهنگ الزامی است',
            'singer_name.required' => 'نام خواننده الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'music_name' => 'required',
            'singer_name' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  سفارش آهنگ شکست خورد",
            ], 400);
        }

        $order = new OrderMusic();
        $order->music_name = $request->music_name;
        $order->singer_name = $request->singer_name;
        $order->user_id = $user->id;
        $order->save();

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "سفارش آهنگ شما با موفقیت ثبت شد.",
        ]);
    }
}

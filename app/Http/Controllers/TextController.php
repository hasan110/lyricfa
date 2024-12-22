<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Models\Music;
use Illuminate\Http\Request;

class TextController extends Controller
{
    public function getTextList(Request $request)
    {
        if ((new UserHelper())->isUserSubscriptionValid($request->header("ApiToken"))) {

            $music = Music::where('id', $request->id_music)->first();
            if (!$music) {
                return response()->json([
                    'data' => null,
                    'errors' => [],
                    'message' => "آهنگ یافت نشد",
                ], 400);
            }

            $texts = $music->texts()->orderBy("start_time")->get();

            return response()->json([
                'data' => $texts,
                'errors' => null,
                'message' => "اطلاعات با موفقیت گرفته شد",
            ]);

        } else {
            return response()->json([
                'data' => null,
                'errors' => null,
                'status' => 1000,
                'message' => "اشتراک شما به پایان رسیده است لطفا اشتراک خود را تمدید کنید",
            ], 400);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Models\Text;
use Illuminate\Http\Request;

class TextController extends Controller
{
    public function getTextList(Request $request)
    {
        if ((new UserHelper())->isUserSubscriptionValid($request->header("ApiToken"))) {

            $id_music = $request->id_music;
            $texts = Text::where('id_music', '=', $id_music)->orderBy("id")->get();

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

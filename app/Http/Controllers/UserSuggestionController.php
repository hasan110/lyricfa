<?php

namespace App\Http\Controllers;

use App\Models\UserSuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserSuggestionController extends Controller
{

    function insertUserSuggestion(Request $request)
    {


        $messsages = array(
            'comment_user.required' => 'توضیحات الزامی است',
            'text_id.required' => 'شناسه متن الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'comment_user' => 'required',
            'text_id' => 'required',
        ], $messsages);


        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن توضیحات شکست خورد"
            ];
            return response()->json($arr, 400);
        }

        $api_token = $request->header("ApiToken");
        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;

            $userSuggestion = new UserSuggestion();
            $userSuggestion->user_id = (int)$user_id;
            $userSuggestion->text_id = (int)$request->text_id;
            $userSuggestion->comment_user = $request->comment_user;

            $userSuggestion->save();

            $arr = [
                'data' => $userSuggestion,
                'errors' => [
                ],
                'message' => "کلمه با موفقیت اضافه شد"
            ];
            return response()->json($arr, 200);
        } else {
            $arr = [
                'data' => null,
                'errors' => [
                ],
                'message' => "کاربر وجود ندارد"
            ];
            return response()->json($arr, 401);
        }

    }


}

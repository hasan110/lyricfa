<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Models\UserSuggestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserSuggestionController extends Controller
{
    function insertUserSuggestion(Request $request)
    {
        $messages = array(
            'comment_user.required' => 'توضیحات الزامی است',
            'text_id.required' => 'شناسه متن الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'comment_user' => 'required',
            'text_id' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن توضیحات شکست خورد"
            ]);
        }

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $userSuggestion = new UserSuggestion();
        $userSuggestion->user_id = $user->id;
        $userSuggestion->text_id = (int)$request->text_id;
        $userSuggestion->comment_user = $request->comment_user;
        $userSuggestion->save();

        return response()->json([
            'data' => $userSuggestion,
            'errors' => [],
            'message' => "پیشنهاد ترجمه با موفقیت ثبت شد"
        ]);
    }


}

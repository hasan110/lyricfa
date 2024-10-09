<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Models\Music;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentMusicController extends Controller
{
    public function getMusicComment(Request $request)
    {
        $messages = array(
            'music_id.required' => 'شناسه موزیک الزامی است',
            'music_id.numeric' => 'شناسه موزیک باید شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'music_id' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  دریافت لیست کامنت شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $music = Music::where('id', $request->music_id)->first();
        if (!$music) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "  موزیک یافت نشد",
            ], 400);
        }

        $response = $music->comments()->where("status", 1)->get();

        foreach (  $response as $item) {
            $user = (new UserHelper())->getUserById($item->user_id);
            $final = "***********";
            if ($user) {
                $first = "0" . substr($user->phone_number,0,3);
                $mid = "****";
                $last = substr($user->phone_number , 7, 3);
                $final = $first . $mid . $last ;
            }
            $item->phone_number = $final;
        }

        return response()->json([
            'data' => $response,
            'errors' => [],
            'message' => "دریافت لیست کامنت موفقیت آمیز بود",
        ]);
    }

    public function addMusicComment(Request $request)
    {
        $messages = array(
            'music_id.required' => 'شناسه موزیک الزامی است',
            'music_id.numeric' => 'شناسه موزیک باید شامل عدد باشد',
            'comment.required' => 'متن کامنت الزامی است',
        );

        $validator = Validator::make($request->all(), [
            'music_id' => 'required|numeric',
            'comment' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  افزودن کامنت کامنت شکست خورد",
            ], 400);
        }

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $music = Music::where('id', $request->music_id)->first();
        if (!$music) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "  موزیک یافت نشد",
            ], 400);
        }

        $music->comments()->create([
            'user_id' => $user->id,
            'comment' => $request->comment
        ]);

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "کامنت بعد از تایید توسط ادمین به نمایش گذاشته میشود ",
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Music;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentMusicController extends Controller
{
    public static function getNumberMusicComment($id_music)
    {
        $music = Music::where('id', $id_music)->first();
        return $music ? $music->comments()->where("status" , 1)->count() : 0;
    }

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
            $arr = [
                'data' => null,
                'errors' => [],
                'message' => "  موزیک یافت نشد",
            ];
            return response()->json($arr, 400);
        }
        $response = $music->comments()->where("status", 1)->get();

        foreach (  $response as $key => $item) {
            $phoneNumber = UserController::getUserById($item->user_id)->phone_number;
            $first = "0" . substr($phoneNumber,0,3);
            $mid = "****";
            $last = substr($phoneNumber , 7, 3);
            $final = $first . $mid . $last ;
            $item->phone_number = $final;
        }

        $arr = [
            'data' => $response,
            'errors' => null,
            'message' => "دریافت لیست کامنت موفقیت آمیز بود",
        ];

        return response()->json($arr);

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
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  افزودن کامنت کامنت شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $api_token = $request->header("ApiToken");
        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;
            $music = Music::where('id', $request->music_id)->first();
            if (!$music) {
                $arr = [
                    'data' => null,
                    'errors' => [],
                    'message' => "  موزیک یافت نشد",
                ];
                return response()->json($arr, 400);
            }

            $music->comments()->create([
                'user_id' => $user_id,
                'comment' => $request->comment
            ]);

            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "کامنت بعد از تایید توسط ادمین به نمایش گذاشته میشود ",
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

    public function editMusicComment(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه کامنت الزامی است',
            'id.numeric' => 'شناسه کامنت باید شامل عدد باشد',
            'comment.required' => 'متن کامنت الزامی است',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'comment' => 'required',
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  افزودن کامنت کامنت شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $comment = $this->commentIsExistInListComments($request);

        if (!isset($comment)) { // array use count
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "چنین کامنتی وجود ندارد جهت ویرایش",
            ];
            return response()->json($arr, 400);
        }

        $comment->comment = $request->comment;
        $comment->status = 0;
        $comment->save();

        $arr = [
            'data' => null,
            'errors' => null,
            'message' => "ویرایش کامنت موفقیت آمیز بود",
        ];

        return response()->json($arr, 200);
    }

    public function removeMusicComment(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه کامنت الزامی است',
            'id.numeric' => 'شناسه کامنت باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  حذف کامنت  شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $comment = $this->commentIsExistInListComments($request);

        if (!isset($comment)) {
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "چنین کامنتی وجود ندارد جهت حذف",
            ];
            return response()->json($arr, 400);
        }

        $comment->delete();

        $arr = [
            'data' => null,
            'errors' => null,
            'message' => "حذف کامنت موفقیت آمیز بود",
        ];
        return response()->json($arr);
    }

    public function commentIsExistInListComments(Request $request)
    {
        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;
            return Comment::where('id', $request->id)->where('user_id', $user_id)->first();
        } else {
            return null;
        }

    }
}

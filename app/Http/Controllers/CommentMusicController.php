<?php

namespace App\Http\Controllers;

use App\Models\CommentMusic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentMusicController extends Controller
{

    public static function getNumberMusicComment($id_music)
    {
        return CommentMusic::where('id_song', $id_music)->where("id_admin_confirmed","!=", 0 )->count();
    }

    public function getMusicComment(Request $request)
    {
        $messsages = array(
            'music_id.required' => 'شناسه موزیک الزامی است',
            'music_id.numeric' => 'شناسه موزیک باید شامل عدد باشد',

        );

        $validator = Validator::make($request->all(), [
            'music_id' => 'required|numeric',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  دریافت لیست کامنت شکست خورد",
            ];
            return response()->json($arr, 400);
        }

       //$response = CommentMusic::where('id_song', $request->music_id)->get();
        $response = CommentMusic::where('id_song', $request->music_id)->where("id_admin_confirmed","!=", 0 )->get();

 
        foreach (  $response as $key => $item) {
            $phoneNumber = UserController::getUserById($item->id_user)->phone_number;
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

        return response()->json($arr, 200);

    }

    public function addMusicComment(Request $request)
    {
        $messsages = array(
            'music_id.required' => 'شناسه موزیک الزامی است',
            'music_id.numeric' => 'شناسه موزیک باید شامل عدد باشد',
            'comment.required' => 'متن کامنت الزامی است',
        );

        $validator = Validator::make($request->all(), [
            'music_id' => 'required|numeric',
            'comment' => 'required',
        ], $messsages);

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
            $comment = new CommentMusic();
            $comment->id_user = $user_id;
            $comment->id_song = (int)$request->music_id;
            $comment->comment = $request->comment;
            $comment->save();

        
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "کامنت بعد از تایید توسط ادمین به نمایش گذاشته میشود ",
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

    public function editMusicComment(Request $request)
    {
        $messsages = array(
            'id.required' => 'شناسه کامنت الزامی است',
            'id.numeric' => 'شناسه کامنت باید شامل عدد باشد',
            'comment.required' => 'متن کامنت الزامی است',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'comment' => 'required',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  افزودن کامنت کامنت شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $name = $this->commentIsExistInListComments($request);

        if (!isset($name)) { // array use count
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "چنین کامنتی وجود ندارد جهت ویرایش",
            ];

            return response()->json($arr, 400);
        }


        $comment = $name;
        $comment->id = (int)$name->id;
        $comment->id_song = (int)$name->id_song;
        $comment->comment = $request->comment;
        $comment->save();

        $arr = [
            'data' => null,
            'errors' => null,
            'message' => "ویرایش کامنت موفقیت آمیز بود",
        ];

        return response()->json($arr, 200);

    }

    public
    function removeMusicComment(Request $request)
    {

        $messsages = array(
            'id.required' => 'شناسه کامنت الزامی است',
            'id.numeric' => 'شناسه کامنت باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  حذف کامنت  شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $name = $this->commentIsExistInListComments($request);

        if (!isset($name)) { // array use count
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "چنین کامنتی وجود ندارد جهت حذف",
            ];

            return response()->json($arr, 400);
        }


        $comment = $name;
        $comment->id = (int)$name->id;
        $comment->id_song = (int)$name->id_song;
        $comment->delete();

        $arr = [
            'data' => null,
            'errors' => null,
            'message' => "حذف کامنت موفقیت آمیز بود",
        ];

        return response()->json($arr, 200);

    }

    public function commentIsExistInListComments(Request $request)
    {


        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;

            return CommentMusic::where('id', $request->id)->where('id_user', $user_id)->first();
        } else {
            return null;
        }

    }
}

<?php

namespace App\Http\Controllers;

use App\Models\CommentSinger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentSingerController extends Controller
{


    public static function getNumberSingerComment($id_singer)
    {
        return CommentSinger::where('id_singer', $id_singer)->count();
    }

    public function getSingerComment(Request $request)
    {
        $messsages = array(
            'singer_id.required' => 'شناسه خواننده الزامی است',
            'singer_id.numeric' => 'شناسه خواننده باید شامل عدد باشد',

        );

        $validator = Validator::make($request->all(), [
            'singer_id' => 'required|numeric',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  دریافت لیست کامنت شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $response = CommentSinger::where('id_singer', $request->singer_id)->get();

        $arr = [
            'data' => $response,
            'errors' => null,
            'message' => "دریافت لیست کامنت موفقیت آمیز بود",
        ];

        return response()->json($arr, 200);

    }

    public function addSingerComment(Request $request)
    {
        $messsages = array(
            'singer_id.required' => 'شناسه خواننده الزامی است',
            'singer_id.numeric' => 'شناسه خواننده باید شامل عدد باشد',
            'comment.required' => 'متن کامنت الزامی است',
        );

        $validator = Validator::make($request->all(), [
            'singer_id' => 'required|numeric',
            'comment' => 'required',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  افزودن کامنت شکست خورد",
            ];
            return response()->json($arr, 400);
        }


        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;

            $comment = new CommentSinger();
            $comment->id_user = $user_id;
            $comment->id_singer = (int)$request->singer_id;
            $comment->comment = $request->comment;
            $comment->save();

            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "افزودن کامنت موفقیت آمیز بود",
            ];

            return response()->json($arr, 200);
        } else {
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "خطا در احراز هویت",
            ];

            return response()->json($arr, 401);
        }
    }

    public function editSingerComment(Request $request)
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
        $comment->id_singer = (int)$name->id_singer;
        $comment->comment = $request->comment;
        $comment->save();

        $arr = [
            'data' => null,
            'errors' => null,
            'message' => "ویرایش کامنت موفقیت آمیز بود",
        ];

        return response()->json($arr, 200);

    }

    public function removeSingerComment(Request $request)
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
        $comment->id_singer = (int)$name->id_singer;
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
            return CommentSinger::where('id', $request->id)->where('id_user', $user_id)->first();
        }else{
            return null;
        }

    }
}

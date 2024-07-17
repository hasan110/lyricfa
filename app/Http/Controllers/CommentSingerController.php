<?php

namespace App\Http\Controllers;

use App\Models\Singer;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentSingerController extends Controller
{
    public static function getNumberSingerComment($id_singer)
    {
        $singer = Singer::where('id', $id_singer)->first();

        return $singer ? $singer->comments()->count() : 0;
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

        $singer = Singer::where('id', $request->singer_id)->first();
        if (!$singer) {
            $arr = [
                'data' => null,
                'errors' => [],
                'message' => "  خواننده یافت نشد",
            ];
            return response()->json($arr, 400);
        }
        $response = $singer->comments;

        $arr = [
            'data' => $response,
            'errors' => null,
            'message' => "دریافت لیست کامنت موفقیت آمیز بود",
        ];

        return response()->json($arr);
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

            $singer = Singer::where('id', $request->singer_id)->first();
            if (!$singer) {
                $arr = [
                    'data' => null,
                    'errors' => [],
                    'message' => "  خواننده یافت نشد",
                ];
                return response()->json($arr, 400);
            }

            $singer->comments()->create([
                'user_id' => $user_id,
                'comment' => $request->comment
            ]);

            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "افزودن کامنت موفقیت آمیز بود",
            ];

            return response()->json($arr);
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

        return response()->json($arr);

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

        $comment = $this->commentIsExistInListComments($request);

        if (!isset($comment)) { // array use count
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
        }else{
            return null;
        }
    }
}

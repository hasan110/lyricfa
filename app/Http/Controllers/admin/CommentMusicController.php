<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Models\Comment;
use App\Models\CommentMusic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\admin\AdminController;
use SebastianBergmann\Diff\LongestCommonSubsequenceCalculator;

class CommentMusicController extends Controller
{
    public function changeStatusMusicComment(Request $request)
    {
        $messages = array(
            'status.required' => 'وضعیت الزامی است',
            'id.required' => 'شناسه کامنت الزامی است',
            'id.numeric' => 'شناسه کامنت باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'status' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  ویرایش کامنت شکست خورد",
            ], 400);
        }

        $admin = AdminController::getAdminByToken($request->header("ApiToken"));
        if(!$admin){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "  ادمین وجود ندارد",
            ], 400);
        }
        $comment = $this->getCommentById($request->id);
        if(!$comment){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "  کامنت وجود ندارد",
            ], 400);
        }

        if($request->status === 1){
            $comment->id_admin_confirmed = $admin->id;
            $comment->status = 1;
            $comment->save();
        }else{
            $comment->delete();
        }

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "تغییر وضعیت کامنت موفقیت آمیز بود",
        ]);
    }

    public function getMusicCommentsNotConfirmed(Request $request)
    {
        $data = Comment::where("status", 0)->with('commentable')->get();

        return response()->json([
            'data' => $data,
            'errors' => null,
            'message' => "دریافت لیست کامنت موفقیت آمیز بود",
        ]);
    }

    public function getCommentById($id)
    {

        return Comment::where('id', $id)->first();

    }
}

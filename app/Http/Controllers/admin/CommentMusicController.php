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
                'message' => "ادمین وجود ندارد",
            ], 400);
        }
        $comment = Comment::where('id', $request->id)->first();
        if(!$comment){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "کامنت وجود ندارد",
            ], 400);
        }

        if($request->status === 1){
            $comment->id_admin_confirmed = $admin->id;
            $comment->status = 1;
            $comment->reply = $request->reply ?? null;
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
        $data = Comment::with('commentable');

        if ($request->sort_by == 'pending') {
            $data = $data->where("status", 0);
        } else if ($request->sort_by == 'confirmed') {
            $data = $data->where("status", 1);
        }

        $search_key = $request->search_key;
        $data = $data->where(function ($query) use ($search_key) {
            $query->where('comment', 'like', '%' . $search_key . '%')
                ->orWhere('reply', 'like', '%' . $search_key . '%');
        })->paginate(50);

        return response()->json([
            'data' => $data,
            'errors' => null,
            'message' => "دریافت لیست کامنت موفقیت آمیز بود",
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Models\Category;
use App\Models\Film;
use App\Models\Music;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    public function getComments(Request $request)
    {
        $messages = array(
            'commentable_type.required' => 'نوع الزامی است',
            'commentable_id.required' => 'شناسه الزامی است',
            'commentable_id.numeric' => 'شناسه باید شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'commentable_type' => 'required',
            'commentable_id' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "دریافت لیست کامنت شکست خورد",
            ], 400);
        }

        $commentable_type = $request->input('commentable_type');
        $commentable_id = $request->input('commentable_id');

        if ($commentable_type === 'music') {
            $model = Music::where('id', $commentable_id)->first();
        } else if ($commentable_type === 'film') {
            $model = Film::where('id', $commentable_id)->first();
        } else if ($commentable_type === 'category') {
            $model = Category::where('id', $commentable_id)->first();
        } else {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "نوع ارسالی معتبر نیست",
            ], 400);
        }

        if (!$model) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "مدل یافت نشد",
            ], 400);
        }

        $comments = $model->comments()->with('user')->where("status", 1)->get();

        foreach ($comments as $item) {
            $final = "***********";
            if ($item->user != null) {
                $user = $item->user;
                if ($user->phone_number) {
                    $first = $user->prefix_code . substr($user->phone_number,0,3);
                } else if ($user->email) {
                    $first = substr($user->email,0,3);
                } else {
                    $first = '';
                }
                $mid = "****";

                if ($user->phone_number) {
                    $last = substr($user->phone_number , -3);
                } else if ($user->email) {
                    $last = substr($user->email,-3);
                } else {
                    $last = '';
                }
                $final = $first . $mid . $last ;
            }
            $item->user_symbol = $final;
        }

        return response()->json([
            'data' => $comments,
            'errors' => [],
            'message' => "لیست نظرات با موفقیت دریافت شد",
        ]);
    }

    public function addComment(Request $request)
    {
        $messages = array(
            'commentable_type.required' => 'نوع الزامی است',
            'commentable_id.required' => 'شناسه الزامی است',
            'commentable_id.numeric' => 'شناسه باید شامل عدد باشد',
            'comment.required' => 'متن نظر الزامی است',
            'comment.max' => 'متن نظر باید کمتر از 500 کاراکتر باشد',
        );

        $validator = Validator::make($request->all(), [
            'comment' => 'required|max:500',
            'commentable_type' => 'required',
            'commentable_id' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن کامنت شکست خورد",
            ], 400);
        }

        $commentable_type = $request->input('commentable_type');
        $commentable_id = $request->input('commentable_id');

        if ($commentable_type === 'music') {
            $model = Music::where('id', $commentable_id)->first();
        } else if ($commentable_type === 'film') {
            $model = Film::where('id', $commentable_id)->first();
        } else if ($commentable_type === 'category') {
            $model = Category::where('id', $commentable_id)->first();
        } else {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "نوع ارسالی معتبر نیست",
            ], 400);
        }

        if (!$model) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "مدل یافت نشد",
            ], 400);
        }

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $model->comments()->create([
            'user_id' => $user->id,
            'comment' => $request->input('comment')
        ]);

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "نظر شما بعد از تایید توسط ادمین به نمایش گذاشته خواهد شد",
        ]);
    }
}

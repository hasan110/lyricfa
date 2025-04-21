<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Models\Category;
use App\Models\Film;
use App\Models\Like;
use App\Models\Music;
use App\Models\Singer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LikeController extends Controller
{
    public function toggleLike(Request $request)
    {
        $messages = array(
            'like.required' => 'ارسال نوع لایک الزامی است',
            'likeable_type.required' => 'نوع الزامی است',
            'likeable_id.required' => 'شناسه الزامی است',
            'likeable_id.numeric' => 'شناسه باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'like' => 'required',
            'likeable_type' => 'required',
            'likeable_id' => 'required|numeric'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "لایک کردن شکست خورد",
            ], 400);
        }
        $likeable_type = $request->input('likeable_type');
        $likeable_id = $request->input('likeable_id');

        if ($likeable_type === 'music') {
            $model = Music::find($likeable_id);
        } else if ($likeable_type === 'singer') {
            $model = Singer::find($likeable_id);
        } else if ($likeable_type === 'film') {
            $model = Film::find($likeable_id);
        } else if ($likeable_type === 'category') {
            $model = Category::find($likeable_id);
        } else {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "نوع ارسالی نامعتبر است",
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
        $like = $model->likes()->where('user_id' , $user->id)->first();

        if (boolval($request->like) === true) {
            if ($like) {
                return response()->json([
                    'data' => null,
                    'errors' => null,
                    'message' => "قبلا لایک شده است",
                ], 400);
            } else {
                $model->likes()->create([
                    'user_id' => $user->id
                ]);
            }
        } else {
            if (!$like) {
                return response()->json([
                    'data' => null,
                    'errors' => null,
                    'message' => "قبلا لایک نشده است",
                ], 400);
            } else {
                $like->delete();
            }
        }

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "لایک کردن موفقیت آمیز بود",
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Models\Music;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LikeMusicController extends Controller
{
    public function addMusicLike(Request $request)
    {
        $messages = array(
            'music_id.required' => 'شناسه آهنگ الزامی است',
            'music_id.numeric' => 'شناسه آهنگ باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'music_id' => 'required|numeric'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "لایک کردن شکست خورد",
            ], 400);
        }

        $check = $this->likeIsExistInListLikes($request);

        if (isset($check)) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " این آهنگ توسط این کاربر قبلا لایک شده است",
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

        $music->likes()->create([
            'user_id' => $user->id
        ]);

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "لایک کردن موفقیت آمیز بود",
        ]);
    }

    public function removeMusicLike(Request $request)
    {
        $messages = array(
            'music_id.required' => 'شناسه آهنگ الزامی است',
            'music_id.numeric' => 'شناسه آهنگ باید شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'music_id' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  لایک کردن شکست خورد",
            ], 400);
        }

        $like = $this->likeIsExistInListLikes($request);

        if (!isset($like)) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " این آهنگ توسط این کاربر لایک نشده است",
            ], 400);
        }

        $like->delete();

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "حذف لایک موفقیت آمیز بود",
        ]);
    }

    public function likeIsExistInListLikes(Request $request)
    {
        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        if ($user) {
            $music = Music::where('id', $request->music_id)->first();
            if (!$music) {
                return null;
            }
            return $music->likes()->where('user_id', $user->id)->first();
        } else {
            return null;
        }
    }

}

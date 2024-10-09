<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Models\Singer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class LikeSingerController extends Controller
{
    public function addSingerLike(Request $request)
    {
        $messages = array(
            'singer_id.required' => 'شناسه خواننده الزامی است',
            'singer_id.numeric' => 'شناسه خواننده باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'singer_id' => 'required|numeric'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  لایک کردن شکست خورد",
            ], 400);
        }

        $check = $this->likeIsExistInListLikes($request);

        if (isset($check)) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " این خواننده توسط این کاربر قبلا لایک شده است",
            ], 400);
        }

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $singer = Singer::where('id', $request->singer_id)->first();
        if (!$singer) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "خواننده یافت نشد",
            ], 400);
        }

        $singer->likes()->create([
            'user_id' => $user->id
        ]);

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "لایک کردن موفقیت آمیز بود",
        ]);
    }

    public function removeSingerLike(Request $request)
    {
        $messages = array(
            'singer_id.required' => 'شناسه خواننده الزامی است',
            'singer_id.numeric' => 'شناسه خواننده باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'singer_id' => 'required|numeric'
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
                'message' => " این خواننده توسط این کاربر لایک نشده است",
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
            $singer = Singer::where('id', $request->singer_id)->first();
            if (!$singer) {
                return null;
            }
            return $singer->likes()->where('user_id', $user->id)->first();
        } else {
            return null;
        }
    }

}

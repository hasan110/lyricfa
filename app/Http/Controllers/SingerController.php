<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Helpers\LikeHelper;
use App\Http\Helpers\SingerHelper;
use App\Http\Helpers\UserHelper;
use App\Models\Singer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SingerController extends Controller
{
    public function getSingersList(Request $request)
    {
        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));
        $per_page = 24;

        $singers = Singer::orderBy('english_name', 'ASC')->
            where('english_name', 'LIKE', "%{$request->search_text}%")->
            orWhere('persian_name', 'LIKE', "%{$request->search_text}%")
        ->paginate($per_page);

        $list = [];
        foreach ($singers as $singer) {
            $num_like = (new SingerHelper())->singerLikesCount($singer->id);
            $list[] = [
                'singer' => $singer,
                'num_like' => $num_like,
                'readable_like' => (new Helper())->readableNumber(intval($num_like)),
                'num_comment' => 0,
                'user_like_it' => (new LikeHelper())->isUserLikeSinger($singer->id, $user->id),
            ];
        }

        $last_page = $singers->lastPage();
        $total = $singers->total();

        return response()->json([
            'data' => [
                'data' => $list,
                'last_page' => $last_page,
                'per_page' => $per_page,
                'total' => $total,
            ],
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getNSingerList(Request $request)
    {
        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));
        $singers = Singer::take(20)->inRandomOrder()->get();

        $list = [];
        foreach ($singers as $singer) {
            $num_like = (new SingerHelper())->singerLikesCount($singer->id);
            $list[] = [
                'singer' => $singer,
                'num_like' => $num_like,
                'num_comment' => 0,
                'user_like_it' => (new LikeHelper())->isUserLikeSinger($singer->id, $user->id)
            ];
        }

        return response()->json([
            'data' => $list,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getSingerByIdWithLike(Request $request)
    {
        $messages = array(
            'singer_id.required' => 'شناسه ی خواننده الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'singer_id' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن لایک شکست خورد"
            ], 400);
        }

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $singer = Singer::where('id', $request->singer_id)->first();
        if (!$singer) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "خواننده یافت نشد"
            ], 400);
        }

        $num_like = (new SingerHelper())->singerLikesCount($singer->id);

        $data = [
            'singer' => $singer,
            'num_like' => $num_like,
            'num_comment' => 0,
            'readable_like' => (new Helper())->readableNumber(intval($num_like)),
            'readable_comment' => 0,
            'user_like_it' => (new LikeHelper())->isUserLikeSinger($singer->id, $user->id)
        ];

        return response()->json([
            'data' => $data,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }
}

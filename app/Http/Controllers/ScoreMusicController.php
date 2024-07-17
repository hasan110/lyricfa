<?php

namespace App\Http\Controllers;

use App\Models\ScoreMusic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScoreMusicController extends Controller
{

    public static function getNumberMusicScore($id_music)
    {
        return ScoreMusic::where('music_id', $id_music)->count();
    }

    public static function getAverageMusicScore($id_music)
    {
        $scoreMusic = ScoreMusic::where('music_id', $id_music);
        if ($scoreMusic)
            return ScoreMusic::where('music_id', $id_music)->avg('score');
        else
            return 0;
    }

    public function getUserScore(Request $request)
    {


        $messsages = array(
            'music_id.required' => 'شناسه آهنگ الزامی است',
            'music_id.numeric' => 'شناسه آهنگ باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'music_id' => 'required|numeric'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  گرفتن امتیاز کاربر",
            ];
            return response()->json($arr, 400);
        }

        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;


            $scoreMusic = ScoreMusic::where('music_id', $request->music_id)->where('user_id', $user_id);
            if ($scoreMusic->count() == 0) {
                $arr = [
                    'data' => 0.0,
                    'errors' => null,
                    'message' => "  کاربر تا کنون امتیاز نداده است",
                ];
                return response()->json($arr, 200);
            } else {
                $arr = [
                    'data' => (float) $scoreMusic->avg('score'),
                    'errors' => null,
                    'message' => "  کاربر امتیاز داده است",
                ];
                return response()->json($arr, 200);

            }
        } else {
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "کاربر احراز هویت نشده است",
            ];

            return response()->json($arr, 401);
        }
    }


    public function addMusicScore(Request $request)
    {

        $messsages = array(
            'music_id.required' => 'شناسه آهنگ الزامی است',
            'music_id.numeric' => 'شناسه آهنگ باید شامل عدد باشد',
            'score.required' => 'امتیاز الزامی است',
            'score.numeric' => 'امتیاز باید عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'music_id' => 'required|numeric',
            'score' => 'required|numeric'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  امتیاز دادن شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $name = $this->scoreIsExist($request);

        if (isset($name)) { // array use count
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => " امتیاز جدید این آهنگ داده شد",
            ];

            $name->score = (int)$request->score;
            $name->save();
            return response()->json($arr, 200);
        }


        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;

            $score = new ScoreMusic();
            $score->music_id = (int)$request->music_id;
            $score->user_id = $user_id;
            $score->score = (int)$request->score;
            $score->save();

            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "امتیاز دادن موفقیت آمیز بود",
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

    public function removeMusicScore(Request $request)
    {

        $messsages = array(
            'music_id.required' => 'شناسه آهنگ الزامی است',
            'music_id.numeric' => 'شناسه آهنگ باید شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'music_id' => 'required|numeric',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  لایک کردن شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $name = $this->likeIsExistInListLikes($request);

        if (!isset($name)) { // array use count
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => " این آهنگ توسط این کاربر لایک نشده است",
            ];

            return response()->json($arr, 400);
        }

        $like = $name;
        $like->delete();

        $arr = [
            'data' => null,
            'errors' => null,
            'message' => "حذف لایک موفقیت آمیز بود",
        ];

        return response()->json($arr, 200);

    }

    public function scoreIsExist(Request $request)
    {

        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;
            return ScoreMusic::where('music_id', $request->music_id)->where('user_id', $user_id)->first();
        } else {
            return null;
        }
    }

}

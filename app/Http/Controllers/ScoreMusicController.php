<?php

namespace App\Http\Controllers;

use App\Http\Helpers\MusicHelper;
use App\Http\Helpers\UserHelper;
use App\Models\ScoreMusic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ScoreMusicController extends Controller
{
    public function getUserScore(Request $request)
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
                'message' => "گرفتن امتیاز کاربر",
            ], 400);
        }

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $scoreMusic = ScoreMusic::where('music_id', $request->music_id)->where('user_id', $user->id);
        if ($scoreMusic->count() == 0) {
            $response = [
                'data' => 0.0,
                'errors' => [],
                'message' => "  کاربر تا کنون امتیاز نداده است",
            ];
        } else {
            $response = [
                'data' => (float) $scoreMusic->avg('score'),
                'errors' => [],
                'message' => "  کاربر امتیاز داده است",
            ];
        }

        return response()->json($response);
    }

    public function addMusicScore(Request $request)
    {
        $messages = array(
            'music_id.required' => 'شناسه آهنگ الزامی است',
            'music_id.numeric' => 'شناسه آهنگ باید شامل عدد باشد',
            'score.required' => 'امتیاز الزامی است',
            'score.numeric' => 'امتیاز باید عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'music_id' => 'required|numeric',
            'score' => 'required|numeric'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  امتیاز دادن شکست خورد",
            ], 400);
        }

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $score = (new MusicHelper())->getUserScoreMusic($user->id , $request->music_id);

        if (isset($score)) {
            $score->score = (int)$request->score;
            $score->save();
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => " امتیاز جدید این آهنگ داده شد",
            ]);
        }

        $score = new ScoreMusic();
        $score->music_id = (int)$request->music_id;
        $score->user_id = $user->id;
        $score->score = (int)$request->score;
        $score->save();

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "امتیاز دادن موفقیت آمیز بود",
        ]);
    }
}

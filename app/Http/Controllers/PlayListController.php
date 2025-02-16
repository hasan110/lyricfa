<?php

namespace App\Http\Controllers;

use App\Http\Helpers\MusicHelper;
use App\Http\Helpers\PlayListHelper;
use App\Http\Helpers\UserHelper;
use App\Models\Music;
use App\Models\PlayList;
use App\Models\PlayListMusic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlayListController extends Controller
{
    function insertUserPlayList(Request $request)
    {
        $messages = array(
            'name.required' => 'نام پلی لیست الزامی است',
            'name.min' => 'حداقل 3 کاراکتر وارد کنید',
            'name.max' => 'حداکثر 30 کاراکتر میتوانید وارد کنید'
        );

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:3|max:30',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن پلی لیست شکست خورد"
            ], 400);
        }

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $check_playList = (new PlayListHelper())->getPlaylistByName($request->name, $user->id);

        if (isset($check_playList)) {
            return response()->json([
                'data' => $check_playList,
                'errors' => [],
                'message' => "لیست پخشی با این اسم وجود دارد"
            ], 400);
        }

        $playList = new PlayList();
        $playList->user_id = $user->id;
        $playList->name = $request->name;
        $playList->save();

        return response()->json([
            'data' => $playList,
            'errors' => [],
            'message' => "لیست پخش با موفقیت اضافه شد"
        ]);
    }

    function getUserPlayList(Request $request)
    {
        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $playlists = PlayList::where("user_id", $user->id)->get();

        foreach ($playlists as $item) {
            $playlist_music_ids = PlayListMusic::where('playlist_id', $item->id)->take(4)->pluck('music_id')->toArray();
            $musics = Music::where('status' , 1)->whereIn('id', $playlist_music_ids)->get();
            $item->musics = (new MusicHelper())->prepareMusicsTemplate($musics);
        }

        return response()->json([
            'data' => $playlists,
            'errors' => [],
            'message' => "لیست پخش با موفقیت دریافت شد"
        ]);
    }

    function removePlayListById(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه پلی لیست الزامی است',
            'id.numeric' => 'شناسه پلی لیست باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "حذف کردن پلی لیست شکست خورد"
            ], 400);
        }

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $playlist = PlayList::where('id', $request->id)->where('user_id', $user->id)->first();
        if (isset($playlist)) {
            $playlist->delete();

            $response = [
                'data' => null,
                'errors' => [],
                'message' => "حذف با موفقیت انجام شد"
            ];
        } else {
            $response = [
                'data' => null,
                'errors' => [],
                'message' => "این شناسه برای حذف وجود ندارد"
            ];
        }

        return response()->json($response);
    }

    function editPlayListById(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه پلی لیست الزامی است',
            'name.required' => 'نام جهت ویرایش الزامی است',
            'id.numeric' => 'شناسه پلی لیست باید شامل عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'name' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش کردن پلی لیست شکست خورد"
            ], 400);
        }

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $playlist = PlayList::where('id', $request->id)->where('user_id', $user->id)->first();
        if (isset($playlist)) {
            $playlist->name = $request->name;
            $playlist->save();

            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "ویرایش با موفقیت انجام شد"
            ]);
        } else {
            return response()->json([
                'data' => null,
                'errors' => [
                ],
                'message' => "این شناسه برای ویرایش وجود ندارد"
            ]);
        }
    }
}

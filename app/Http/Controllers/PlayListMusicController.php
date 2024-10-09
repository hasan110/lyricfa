<?php

namespace App\Http\Controllers;

use App\Http\Helpers\MusicHelper;
use App\Http\Helpers\PlayListHelper;
use App\Models\Music;
use App\Models\PlayListMusic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlayListMusicController extends Controller
{
    public function getAllMusicWithPlayList(Request $request)
    {
        $messages = array(
            'playlist_id.required' => 'شناسه کاربر الزامی است',
            'playlist_id.numeric' => 'شناسه کاربر باید شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'playlist_id' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => null,
            ], 400);
        }

        $playlist_music_ids = PlayListMusic::where('playlist_id', $request->playlist_id)->pluck('music_id')->toArray();

        $musics = Music::where('status' , 1)->where('name', 'LIKE', "%{$request->search_text}%")->orWhere('persian_name', 'LIKE', "%{$request->search_text}%")->orderBy('id', 'ASC')->paginate(24);

        $list = (new MusicHelper())->prepareMusicsTemplate($musics, null, $playlist_music_ids);
        $last_page = $musics->lastPage();
        $total = $musics->total();

        return response()->json([
            'data' => [
                'data' => $list,
                'last_page' => $last_page,
                'total' => $total,
            ],
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getMusicPlayListUser(Request $request)
    {
        $messages = array(
            'playlist_id.required' => 'شناسه کاربر الزامی است',
            'playlist_id.numeric' => 'شناسه کاربر باید شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'playlist_id' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => null,
            ], 400);
        }

        $music_ids = [];

        $playlist_musics = PlayListMusic::where('playlist_id', $request->playlist_id)->get();
        foreach ($playlist_musics as $item) {
            $music_ids[] = $item->music_id;
        }

        $musics = Music::where('status' , 1)->whereIn('id', $music_ids)->get();
        $list = (new MusicHelper())->prepareMusicsTemplate($musics);

        return response()->json([
            'data' => $list,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function insertMusicsPlayList(Request $request)
    {
        $messages = array(
            'playlist_id.required' => 'شناسه پلی لیست الزامی است',
            'playlist_id.numeric' => 'شناسه پلی لیست باید شامل عدد باشد',
            'musics.array' => 'لیست آهنگ باید آرایه ای از شناسه ی آهنگ ها باشد',
            'remove_musics.array' => 'لیست آهنگ های حذفی باید آرایه ای از شناسه ی آهنگ ها باشد',
        );

        $validator = Validator::make($request->all(), [
            'playlist_id' => 'required|numeric',
            'musics' => 'array',
            'remove_musics' => 'array',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن پلی لیست شکست خورد",
            ], 400);
        }

        if($request->musics){
            foreach ($request->musics as $item) {
                $check = (new PlayListHelper())->checkMusicExistsInPlayList($request->playlist_id, $item);

                if (!$check) {
                    PlayListMusic::create([
                        'playlist_id' => (int) $request->playlist_id,
                        'music_id' => (int) $item,
                    ]);
                }
            }
        }

        if($request->remove_musics){
            foreach ($request->remove_musics as $item) {
                $playlist_music = (new PlayListHelper())->checkMusicExistsInPlayList($request->playlist_id, $item);

                if (isset($playlist_music)) {
                    $playlist_music->delete();
                }
            }
        }

        return response()->json([
            'data' => null,
            'errors' => [],
            'message' => "عملیات با موفقیت انجام شد",
        ]);
    }

    public function removeMusicFromPlayList(Request $request){

        $messages = array(
            'playlist_id.required' => 'شناسه پلی لیست الزامی است',
            'playlist_id.numeric' => 'شناسه پلی لیست باید شامل عدد باشد',
            'music_id.required' => 'شناسه آهنگ الزامی است',
            'music_id.numeric' => 'شناسه آهنگ لیست باید شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'playlist_id' => 'required|numeric',
            'music_id' => 'required|numeric'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "حذف آهنگ موفقیت آمیز نبود",
            ], 400);
        }

        $check = (new PlayListHelper())->checkMusicExistsInPlayList($request->playlist_id , $request->music_id);
        if ($check != null) {
            $check->delete();
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "حذف موفقیت آمیز بود",
            ]);
        } else {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "چنین آهنگی در لیست وجود ندارد",
            ], 400);
        }
    }
}

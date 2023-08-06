<?php

namespace App\Http\Controllers;

use App\Models\PlayListMusic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlayListMusicController extends Controller
{

    public function insertMusicPlayList(Request $request)
    {

        $messsages = array(
            'user_id.required' => 'شناسه کاربر الزامی است',
            'user_id.numeric' => 'شناسه کاربر باید شامل عدد باشد',

            'music_id.required' => 'شناسه آهنگ الزامی است',
            'music_id.numeric' => 'شناسه آهنگ باید شامل عدد باشد',

        );

        $validator = Validator::make($request->all(), [
            'user_id' => 'required|numeric',
            'music_id' => 'required|numeric',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن پلی لیست شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $name = $this->userMusicExistInPlayList($request);

        if (isset($name)) { // array use count
            $arr = [
                'data' => $name,
                'errors' => [
                ],
                'message' => "این آهنگ قبلا در این لیست پخش اضافه شده",
            ];

            return response()->json($arr, 400);
        }

        $playList = new PlayListMusic;
        $playList->user_id = (int) $request->user_id;
        $playList->music_id = (int) $request->music_id;
        $playList->save();

        $arr = [
            'data' => $playList,
            'errors' => [
            ],
            'message' => "لیست پخش با موفقیت اضافه شد",
        ];
        return response()->json($arr, 200);
    }

    public function getAllMusicWithPlayList(Request $request)
    {

        $messsages = array(
            'playlist_id.required' => 'شناسه کاربر الزامی است',
            'playlist_id.numeric' => 'شناسه کاربر باید شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'playlist_id' => 'required|numeric',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => null,
            ];
            return response()->json($arr, 400);
        }

        $listMusic = [];

        $musics = PlayListMusic::where('playlist_id', $request->playlist_id)->get();
        foreach ($musics as $item) {
            $listMusic[] = $item->music_id;
        }

        return MusicController::getAllMusicWithPlayList($listMusic, $request->search_text);
    }

    public function getMusicPlayListUser(Request $request)
    {

        $messsages = array(
            'playlist_id.required' => 'شناسه کاربر الزامی است',
            'playlist_id.numeric' => 'شناسه کاربر باید شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'playlist_id' => 'required|numeric',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => null,
            ];
            return response()->json($arr, 400);
        }

        $listMusic = [];

        $musics = PlayListMusic::where('playlist_id', $request->playlist_id)->get();
        foreach ($musics as $item) {
            $listMusic[] = $item->music_id;
        }

        return MusicController::getListMusicByIds($listMusic);
    }

    public static function getRandom4Music($playListId){
        $listMusic = [];
        $musics = PlayListMusic::where('playlist_id', $playListId)->take(4)->get();
        foreach ($musics as $item) {
                $listMusic[] = $item->music_id;
        }

        return MusicController::getListMusicByIdsOnlyMusics($listMusic);
    }

    public function userMusicExistInPlayList(Request $request)
    {
        return PlayListMusic::where('music_id', $request->music_id)->where('user_id', $request->user_id)->first();
    }

    public function userMusicExistInPlayList2($playlist_id, $music_id)
    {
        return PlayListMusic::where('music_id', $music_id)->where('playlist_id', $playlist_id)->first();
    }

    public function insertMusicsPlayList(Request $request)
    {

        $messsages = array(
            'playlist_id.required' => 'شناسه پلی لیست الزامی است',
            'playlist_id.numeric' => 'شناسه پلی لیست باید شامل عدد باشد',

            'musics.array' => 'لیست آهنگ باید آرایه ای از شناسه ی آهنگ ها باشد',
            'remove_musics.array' => 'لیست آهنگ های حذفی باید آرایه ای از شناسه ی آهنگ ها باشد',

        );

        $validator = Validator::make($request->all(), [
            'playlist_id' => 'required|numeric',
            'musics' => 'array',
            'remove_musics' => 'array',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن پلی لیست شکست خورد",
            ];
            return response()->json($arr, 400);
        }

         $playList = new PlayListMusic;
        if($request->musics){
            foreach ($request->musics as $item) {
                $name = $this->userMusicExistInPlayList2($request->playlist_id, $item);

                if (!$name) {
                    $playList = PlayListMusic::create([
                        'playlist_id' => (int) $request->playlist_id,
                        'music_id' => (int) $item,
                    ]);
                }

            }
        }


        if($request->remove_musics){
            foreach ($request->remove_musics as $item) {
                $name = $this->userMusicExistInPlayList2($request->playlist_id, $item);

                if (isset($name)) {
                    $name->delete();
                } else {}
            }
        }

        $arr = [
            'data' => null,
            'errors' => [
            ],
            'message' => "لیست پخش با موفقیت اضافه شد",
        ];
        return response()->json($arr, 200);
    }

    public function removeMusicFromPlayList(Request $request){

        $messsages = array(
            'playlist_id.required' => 'شناسه پلی لیست الزامی است',
            'playlist_id.numeric' => 'شناسه پلی لیست باید شامل عدد باشد',

            'music_id.required' => 'شناسه آهنگ الزامی است',
            'music_id.numeric' => 'شناسه آهنگ لیست باید شامل عدد باشد',

        );

        $validator = Validator::make($request->all(), [
            'playlist_id' => 'required|numeric',
            'music_id' => 'required|numeric'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "حذف آهنگ موفقیت آمیز نبود",
            ];
            return response()->json($arr, 400);
        }

        $music = $this->musicIsExistInPlayList($request);
        if($music!= null){
         $music->delete();
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "حذف موفقیت آمیز بود",
            ];
            return response()->json($arr, 200);
        }else{
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "چنین آهنگی در لیست وجود ندارد",
            ];
            return response()->json($arr, 400);
        }
    }


    public function musicIsExistInPlayList(Request $request)
    {


        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            return PlayListMusic::where('music_id', $request->music_id)->where('playlist_id', $request->playlist_id)->first();
        } else {
            return null;
        }

    }

}

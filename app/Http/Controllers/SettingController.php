<?php

namespace App\Http\Controllers;

use App\Models\LikeMusic;
use App\Models\LikeSinger;
use App\Models\Like;
use App\Models\Music;
use App\Models\Setting;
use App\Models\Singer;
use Hamcrest\Core\Set;
use Illuminate\Http\Request;


class SettingController extends Controller
{

    public function getSetting()
    {
        $settings = Setting::query()->where('is_public' , '=' ,1)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->value;
        }

        $response = [
            'data' => $result,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد"
        ];
        return response()->json($response);
    }

    public function like_movements()
    {
        ini_set('max_execution_time', '1500');
        foreach (LikeMusic::all() as $like_music) {
            Like::create([
                'user_id' => $like_music->id_user,
                'likeable_id' => $like_music->id_song,
                'likeable_type' => 'App\Models\Music',
            ]);
        }
        foreach (LikeSinger::all() as $like_singer) {
            Like::create([
                'user_id' => $like_singer->id_user,
                'likeable_id' => $like_singer->id_singer,
                'likeable_type' => 'App\Models\Singer',
            ]);
        }

        return response()->json(['data' => 'finished']);
    }

    public function singer_movements()
    {
        ini_set('max_execution_time', '1500');
        foreach (Music::all() as $music) {
            foreach (explode(',', $music['singers']) as $item) {
                $singer = Singer::where('id', $item)->first();

                $music->singers()->attach($singer);
            }
        }

        return response()->json(['data' => 'finished']);
    }

}

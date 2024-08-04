<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AlbumController extends Controller
{
    public static function getAlbumById($album_id)
    {
        $arr = [];
        foreach (explode(',', $album_id) as $item) {
            $album = Album::where('id', $item)->first();
            $album['album_name_english'] = $album->english_name;
            $album['album_name_persian'] = $album->persian_name;
            $album['image_url'] = 'albums/'.$album->id.'.png';
            $album['create_time'] = $album->published_at;
            $arr[] = $album;
        }
        return $arr;
    }

    public function getAlbumsList(Request $request)
    {
        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        $user_id  = $user->id;
        if ($user_id) {
            $albums = Album::select(
                ['id','english_name AS album_name_english', 'persian_name AS album_name_persian', 'published_at AS create_time', 'created_at', 'updated_at']
            )->orderBy('id', 'DESC')->
            where('english_name', 'LIKE', "%{$request->search_text}%")->
            orWhere('persian_name', 'LIKE', "%{$request->search_text}%")->
            paginate(25);
            $response = [
                'data' => $albums,
                'errors' => [
                ],
                'message' => "اطلاعات با موفقیت گرفته شد",
            ];
            return response()->json($response, 200);
        }else{
            $response = [
                'data' => null,
                'errors' => [
                ],
                'message' => "مشکل در احراز هویت",
            ];
            return response()->json($response, 401);
        }
    }

    public function getNAlbumList(Request $request)
    {
        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        $user_id  = $user->id;
        if($user_id) {
            $albums = Album::select(
                ['id','english_name AS album_name_english', 'persian_name AS album_name_persian', 'published_at AS create_time', 'created_at', 'updated_at']
            )->orderBy('id', 'DESC')->take(10)->get();
            $response = [
                'data' => $albums,
                'errors' => [
                ],
                'message' => "اطلاعات با موفقیت گرفته شد",
            ];
            return response()->json($response, 200);
        }else{
            $response = [
                'data' => null,
                'errors' => [
                ],
                'message' => "مشکل در احراز هویت",
            ];
            return response()->json($response, 200);
        }
    }

    public function getAlbumData(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'album_id' => 'required',
        ], array(
            'album_id.required' => 'شناسه ی آلبوم الزامی است'
        ));

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "دریافت اطلاعات آلبوم شکست  خورد"
            ], 400);
        }

        $album = Album::select(
            ['id','english_name AS album_name_english', 'persian_name AS album_name_persian', 'published_at AS create_time', 'created_at', 'updated_at']
        )->where('id' , $request->album_id)->first();

        return response()->json([
            'data' => $album,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

}

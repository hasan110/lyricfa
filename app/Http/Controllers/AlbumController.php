<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    public static function getAlbumById($album_id)
    {


        $arr = [];
        foreach (explode(',', $album_id) as $item) {
            $arr[] = Album::where('id', $item)->first();
        }


        return $arr;

    }

    public function getAlbumsList(Request $request)
    {


        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        $user_id  = $user->id;
        if ($user_id) {
            $albums = Album::orderBy('id', 'DESC')->
            where('album_name_english', 'LIKE', "%{$request->search_text}%")->
            orWhere('album_name_english', 'LIKE', "%{$request->search_text}%")->
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
            $albums = Album::orderBy('id', 'DESC')->take(10)->get();
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

}

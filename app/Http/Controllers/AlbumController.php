<?php

namespace App\Http\Controllers;

use App\Models\Album;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AlbumController extends Controller
{
    public function getAlbumsList(Request $request)
    {
        $albums = Album::select(
            ['id','english_name AS album_name_english', 'persian_name AS album_name_persian', 'published_at AS create_time', 'created_at', 'updated_at']
        )->orderBy('id', 'DESC')->
        where('english_name', 'LIKE', "%{$request->search_text}%")->
        orWhere('persian_name', 'LIKE', "%{$request->search_text}%")->
        paginate(25);

        return response()->json([
            'data' => $albums,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getNAlbumList(Request $request)
    {
        $albums = Album::select(
            ['id','english_name AS album_name_english', 'persian_name AS album_name_persian', 'published_at AS create_time', 'created_at', 'updated_at']
        )->orderBy('id', 'DESC')->take(10)->get();

        return response()->json([
            'data' => $albums,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
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

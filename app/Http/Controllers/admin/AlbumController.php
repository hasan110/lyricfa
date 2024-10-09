<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\SingerController;
use App\Models\Album;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AlbumController extends Controller
{
    public static function getAlbumById($album_id)
    {
        return Album::where('id', $album_id)->first();
    }

    public function getAlbum(Request $request)
    {
        $messages = array(
            'id.required' => 'id نمی تواند خالی باشد',
            'id.numeric' => 'id باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " گرفتن اطلاعات آلبوم شکست خورد",
            ], 400);
        }

        $album= Album::where('id', $request->id)->first();
        $album->singers = $album->singers()->pluck('id')->toArray();

        return response()->json([
            'data' => $album,
            'errors' => null,
            'message' => " گرفتن اطلاعات موفقیت آمیز بود",
        ]);
    }

    public function albumsList(Request $request)
    {
        $albums = Album::orderBy('id', 'DESC')->
            where('english_name', 'LIKE', "%{$request->search_text}%")->
            orWhere('persian_name', 'LIKE', "%{$request->search_text}%")->paginate(50);

        return response()->json([
            'data' => $albums,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }


    public function albumsUpdate(Request $request)
    {
        $messages = array(
            'id.required' => 'id نمی تواند خالی باشد',
            'album_name_english.required' => 'album_name_english نمی تواند خالی باشد',
            'album_name_persian.required' => 'album_name_persian نمی تواند خالی باشد',
            'image_url.required' => 'عکس آلبوم اجباری است',
            'image_url.file' => 'نوع عکس باید فایل باشد',
            'image_url.mimes' => 'نوع فایل باید png باشد',
            'image_url.dimensions' => 'عکس باید 300 در 300 باشد',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'album_name_english' => 'required',
            'album_name_persian' => 'required',
            'image_url' => 'file|mimes:png|dimensions:min_width=300,min_height=300,max_width=300,max_height=300'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " به روز رسانی آلبوم شکست خورد",
            ], 400);
        }

        $album = $this->getAlbumById($request->id);
        if (!$album) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "این آلبوم وجود ندارد برای به روز رسانی"
            ], 400);
        }

        $album->english_name = $request->album_name_english;
        $album->persian_name = $request->album_name_persian;
        $album->save();

        if ($request->hasFile('image_url')) {
            $this->uploadFileById($request->image_url, "albums", $album->id);
        }

        if ($request->singers) {
            $album->singers()->sync(explode(',', $request->singers));
        }

        return response()->json([
            'data' => $album,
            'errors' => null,
            'message' => "آلبوم با موفقیت به روز رسانی شد"
        ]);
    }


    public function albumsCreate(Request $request)
    {
        $messages = array(
            'album_name_english.required' => 'album_name_english نمی تواند خالی باشد',
            'album_name_persian.required' => 'album_name_persian نمی تواند خالی باشد',
            'singers.required' => 'فیلد خواننده ها الزامی می باشد.',
            'image_url.required' => 'عکس آلبوم اجباری است',
            'image_url.file' => 'نوع عکس باید فایل باشد',
            'image_url.mimes' => 'نوع فایل باید png باشد',
            'image_url.dimensions' => 'عکس باید 300 در 300 باشد',
        );

        $validator = Validator::make($request->all(), [
            'album_name_english' => 'required',
            'album_name_persian' => 'required',
            // 'singers' => 'required',
            'image_url' => 'required|file|mimes:png|dimensions:min_width=300,min_height=300,max_width=300,max_height=300'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " ایجاد آلبوم شکست خورد",
            ], 400);
        }

        $album = new Album();
        $album->english_name = $request->album_name_english;
        $album->persian_name = $request->album_name_persian;
        $album->save();
        if ($request->hasFile('image_url')) {
            $this->uploadFileById($request->image_url, "albums", $album->id);
        }
        if ($request->singers) {
            $album->singers()->attach(explode(',', $request->singers));
        }

        return response()->json([
            'data' => $album,
            'errors' => null,
            'message' => "آلبوم با موفقیت ایجاد شد"
        ]);
    }
}

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
//        $arr = [];
//        foreach (explode(',', $album_id) as $item) {
//            $arr[] = Album::where('id', $item)->first();
//        }
//        return $arr;

        return Album::where('id', $album_id)->first();
    }

    public function getAlbum(Request $request)
    {

        $messsages = array(
            'id.required' => 'id نمی تواند خالی باشد',
            'id.numeric' => 'id باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " گرفتن اطلاعات آلبوم شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $album= Album::where('id', $request->id)->first();
        $singers = [];
        if($album->singers){
            $singer_id = $album->singers;

            $singer = SingerController::getSingerById($singer_id);
            foreach ($singer as $item){
                $singers[] = $item->id;
            }
        }
        $album->singers = $singers;

        $arr = [
            'data' => $album,
            'errors' => null,
            'message' => " گرفتن اطلاعات موفقیت آمیز بود",
        ];
        return response()->json($arr, 200);
    }

    public function albumsList(Request $request)
    {
        $albums = Album::orderBy('id', 'DESC')->
        where('album_name_english', 'LIKE', "%{$request->search_text}%")->
        orWhere('album_name_english', 'LIKE', "%{$request->search_text}%")->paginate(50);
        $response = [
            'data' => $albums,
            'errors' => [
            ],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);
    }


    public function albumsUpdate(Request $request)
    {
        $messsages = array(
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
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " به روز رسانی آلبوم شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $album = $this->getAlbumById($request->id);
        if (!$album) {

            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "این آلبوم وجود ندارد برای به روز رسانی"
            ];

            return response()->json($arr, 400);
        }


        $album->album_name_english = $request->album_name_english;
        $album->album_name_persian = $request->album_name_persian;
        $album->image_url = 'albums/' .$album->id .'.png';
        $album->save();

        if ($request->hasFile('image_url')) {
            $this->uploadFileById($request->image_url, "albums", $album->id);
        }

        $arr = [
            'data' => $album,
            'errors' => null,
            'message' => "آلبوم با موفقیت به روز رسانی شد"
        ];
        return response()->json($arr, 200);
    }


    public function albumsCreate(Request $request)
    {
        $messsages = array(

            'album_name_english.required' => 'album_name_english نمی تواند خالی باشد',
            'album_name_persian.required' => 'album_name_persian نمی تواند خالی باشد',
            'image_url.required' => 'عکس آلبوم اجباری است',
            'image_url.file' => 'نوع عکس باید فایل باشد',
            'image_url.mimes' => 'نوع فایل باید png باشد',
            'image_url.dimensions' => 'عکس باید 300 در 300 باشد',
        );

        $validator = Validator::make($request->all(), [
            'album_name_english' => 'required',
            'album_name_persian' => 'required',
            'image_url' => 'required|file|mimes:png|dimensions:min_width=300,min_height=300,max_width=300,max_height=300'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " ایجاد آلبوم شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $album = new Album();
        $album->album_name_english = $request->album_name_english;
        $album->album_name_persian = $request->album_name_persian;
        $album->save();
        $album->image_url = 'albums/' .$album->id .'.png';
        $album->save();
        if ($request->hasFile('image_url')) {
            $this->uploadFileById($request->image_url, "albums", $album->id);
        }

        $arr = [
            'data' => $album,
            'errors' => null,
            'message' => "آلبوم با موفقیت ایجاد شد"
        ];
        return response()->json($arr, 200);
    }

}

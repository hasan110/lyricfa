<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\CommentMusicController;
use App\Http\Controllers\CommentSingerController;
use App\Http\Controllers\LikeMusicController;
use App\Http\Controllers\LikeSingerController;
use App\Models\Music;
use App\Models\Singer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class SingerController extends Controller
{
    public function SingersList(Request $request)
    {
        $list = Singer::whereNotNull('id');

        if ($request->search_key) {
            $list = $list->where('english_name', 'LIKE', "%{$request->search_key}%")->
            orWhere('persian_name', 'LIKE', "%{$request->search_key}%")->
            orWhere('id', '=', $request->search_key);
        }
        if ($request->sort_by) {
            switch ($request->sort_by) {
                case 'newest':
                default:
                    $list = $list->orderBy('created_at', 'desc');
                    break;
                case 'oldest':
                    $list = $list->orderBy('created_at', 'asc');
                    break;
                case 'persian_name':
                    $list = $list->orderBy('persian_name', 'asc');
                    break;
                case 'english_name':
                    $list = $list->orderBy('english_name', 'asc');
                    break;
            }
        }
        $list = $list->paginate(50);

        if ($request->no_page) {
            $new_list = Singer::whereIn('id', $request->singer_ids ?? [1])->get();
            $list = $list->merge($new_list);
        }

        foreach ($list as $item) {
            $item->num_like = LikeSingerController::getNumberSingerLike($item->id);
            $item->num_musics = $item->musics()->count();
        }

        $response = [
            'data' => $list,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response);
    }

    public function singersCreate(Request $request)
    {
        $messages = array(
            'english_name.required' => 'نام انگلیسی خواننده نمی تواند خالی باشد',
            'persian_name.required' => 'نام فارسی خواننده نمی تواند خالی باشد',
            'image.required' => 'عکس خواننده اجباری است',
            'image.file' => 'نوع عکس باید فایل باشد',
            'image.mimes' => 'نوع فایل باید jpg باشد',
            'image.dimensions' => 'عکس باید 300 در 300 باشد',
        );

        $validator = Validator::make($request->all(), [
            'english_name' => 'required',
            'persian_name' => 'required',
            'image' => 'required|file|mimes:jpg|dimensions:min_width=300,min_height=300,max_width=300,max_height=300'
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " افزودن خواننده شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $singer = new Singer();
        $singer->english_name = $request->english_name;
        $singer->persian_name = $request->persian_name;

        $singer->save();

        if ($request->hasFile('image')) {
            $this->uploadFileById($request->image, "singers", $singer->id);
        }

        $arr = [
            'data' => $singer,
            'errors' => null,
            'message' => "موزیک با موفقیت اضافه شد"
        ];
        return response()->json($arr);
    }

    public function singersUpdate(Request $request)
    {
        $messages = array(

            'id.required' => 'id نمی تواند خالی باشد',
            'id.numeric' => 'id باید فقط شامل عدد باشد',
            'english_name.required' => 'نام انگلیسی خواننده نمی تواند خالی باشد',
            'persian_name.required' => 'نام فارسی خواننده نمی تواند خالی باشد',
            'image.file' => 'نوع عکس باید فایل باشد',
            'image.mimes' => 'نوع فایل باید jpg باشد',
            'image.dimensions' => 'عکس باید 300 در 300 باشد',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'english_name' => 'required',
            'persian_name' => 'required',
            'image' => 'file|mimes:jpg|dimensions:min_width=300,min_height=300,max_width=300,max_height=300'
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " افزودن خواننده شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $singer = $this->getSingerById($request->id);
        if (!$singer) {

            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "این خواننده وجود ندارد برای به روز رسانی"
            ];

            return response()->json($arr);
        }
        $singer->english_name = $request->english_name;
        $singer->persian_name = $request->persian_name;

        $singer->save();

        if ($request->hasFile('image')) {
            $this->uploadFileById($request->image, "singers", $singer->id);
        }

        $arr = [
            'data' => $singer,
            'errors' => null,
            'message' => "خواننده با موفقیت به روز رسانی شد"
        ];

        return response()->json($arr);
    }

    public static function getSingerById($id)
    {
        return Singer::where('id', $id)->first();
    }

    public function getSinger(Request $request)
    {
        $messages = array(
            'id.required' => 'id نمی تواند خالی باشد',
            'id.numeric' => 'id باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " گرفتن اطلاعات خواننده خواننده شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $get_singer= Singer::where('id', $request->id)->first();

        $arr = [
            'data' => $get_singer,
            'errors' => null,
            'message' => " گرفتن اطلاعات موفقیت آمیز بود",
        ];
        return response()->json($arr, 200);
    }
}

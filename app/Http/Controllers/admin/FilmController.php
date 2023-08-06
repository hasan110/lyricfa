<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Film;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FilmController extends Controller
{

    public function getListForShow(Request $request)
    {

        $search_key = $request->search_text;

        $films = Film::orderBy('id', "DESC")->whereIn('type', [ 1, 2]);
        $films = $films->where(function ($query) use ($search_key) {
            $query->where('english_name', 'like', '%' . $search_key . '%')
                ->orWhere('persian_name', 'like', '%' . $search_key . '%');
        })->paginate(25);
        $response = [
            'data' => $films,
            'errors' => [
            ],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);
    }

    public function getChildById(Request $request)
    {

        $messsages = array(
            'id.required' => 'id الزامی است',
            'id.numeric' => 'id باید عدد باشد'

        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $messsages);


        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن اطلاعات شکست خورد"
            ];
            return response()->json($arr, 400);
        }

        $id = $request->id;

        $films = Film::orderBy('id', "DESC")->where('parent', $id)->whereIn('type', [3, 4])->get();
        $response = [
            'data' => $films,
            'errors' => [
            ],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);
    }

    public static function getFilmById($id)
    {

        $get_music = Film::where('id', $id)->first();

        return $get_music;
    }

    public function filmCreate(Request $request)
    {
        $messsages = array(
            'english_name.required' => 'عنوان انگلیسی آهنگ اجباری است',
            'persian_name.required' => 'عنوان فارسی آهنگ اجباری است',
            'type.required' => 'type لازم است.',
            'parent.required' => 'parent لازم است',
            'type.numeric' => 'type عدد می باشد',
            'parent.numeric' => 'parent باید عدد باشد',
            'film.file' => 'نوع فیلم باید فایل باشد',
            'film.mimetypes' => 'نوع فایل باید ویدئو باشد',
            'image.file' => 'نوع عکس باید فایل باشد',
            'image.mimes' => 'نوع فایل باید jpg باشد',
        );

        $validator = Validator::make($request->all(), [
            'english_name' => 'required',
            'persian_name' => 'required',
            'type' => 'required|numeric',
            'parent' => 'required|numeric',
            'film' => 'file|mimetypes:video/*',
            'image' => 'file|mimes:jpg'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " افزودن فیلم شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $film = new Film();
        $film->english_name = $request->english_name;
        $film->persian_name = $request->persian_name;
        $film->type = $request->type;
        $film->parent = $request->parent;
        $film->save();

        if ($request->hasFile('film')) {
            $this->uploadFileById($request->film,"films", $film->id);
        }
        if ($request->hasFile('image')) {
            $this->uploadFileById($request->image,"films_banner", $film->id);
        }

        $arr = [
            'data' => $film,
            'errors' => null,
            'message' => "فیلم با موفقیت اضافه شد"
        ];

        return response()->json($arr, 200);


    }

    public function filmUpdate(Request $request)
    {
        $messsages = array(

            'id.required' => 'id نمی تواند خالی باشد',
            'id.numeric' => 'id باید فقط شامل عدد باشد',
            'english_name.required' => 'عنوان انگلیسی آهنگ اجباری است',
            'persian_name.required' => 'عنوان فارسی آهنگ اجباری است',
            'type.required' => 'type لازم است.',
            'parent.required' => 'parent لازم است',
            'type.numeric' => 'type عدد می باشد',
            'parent.numeric' => 'parent باید عدد باشد',
            'film.file' => 'نوع موزیک باید فایل باشد',
            'film.mimeTypes' => 'نوع فایل باید ویدئو باشد',
            'image.file' => 'نوع عکس باید فایل باشد',
            'image.mimes' => 'نوع فایل باید jpg باشد',
        );

        $validator = Validator::make($request->all(), [
            'id'=> 'required|numeric',
            'english_name' => 'required',
            'persian_name' => 'required',
            'type' => 'required|numeric',
            'parent' => 'required|numeric',
            'film' => 'file|mimeTypes:video',
            'image' => 'file|mimes:jpg'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " ویرایش فیلم شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $film = $this->getFilmById($request->id);
        if(!$film){

            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "این فیلم وجود ندارد برای به روز رسانی"
            ];

            return response()->json($arr, 200);
        }
        $film->english_name = $request->english_name;
        $film->persian_name = $request->persian_name;
        $film->type = $request->type;
        $film->parent = $request->parent;
        $film->save();

        if ($request->hasFile('film')) {
            $this->uploadFileById($request->film,"films", $film->id);
        }

        if ($request->hasFile('image')) {
            $this->uploadFileById($request->image,"films_banner", $film->id);
        }

        $arr = [
            'data' => $film,
            'errors' => null,
            'message' => "موزیک با موفقیت اضافه شد"
        ];

        return response()->json($arr, 200);


    }


    public function getFilmByIdRequest(Request $request)
    {
        $id = $request->id;
        $film = $this->getFilmById($id);

        $arr = [
            'data' => $film,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];

        return response()->json($arr, 200);

    }



}


/*
 * type = 1 -> is Movie (One part film)
 * type = 2 -> is Serial (Some of Chapter)
 * type = 3 -> is Chapter (Some of film)
 * type = 4 -> is Film (Chapter or serials Films)
 */

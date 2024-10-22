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

        $films = Film::orderBy('id', "DESC");

        if ($request->type) {
            $films = $films->where('type', $request->type);
        } else {
            $films = $films->whereIn('type', [1, 2]);
        }

        if ($request->series_id) {
            $films = $films->where('parent', $request->series_id);
        }

        $films = $films->where(function ($query) use ($search_key) {
            $query->where('english_name', 'like', '%' . $search_key . '%')
                ->orWhere('persian_name', 'like', '%' . $search_key . '%')
                ->orWhere('id', '=', $search_key);
        })->paginate(25);

        return response()->json([
            'data' => $films,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getChildById(Request $request)
    {
        $messages = array(
            'id.required' => 'id الزامی است',
            'id.numeric' => 'id باید عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن اطلاعات شکست خورد"
            ], 400);
        }

        $id = $request->id;

        $films = Film::orderBy('id', "DESC")->where('parent', $id)->whereIn('type', [3, 4, 5])->get();

        return response()->json([
            'data' => $films,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public static function getFilmById($id)
    {
        return Film::where('id', $id)->first();
    }

    public function filmCreate(Request $request)
    {
        $messages = array(
            'english_name.required' => 'عنوان انگلیسی فیلم اجباری است',
            'persian_name.required' => 'عنوان فارسی فیلم اجباری است',
            'type.required' => 'نوع فیلم لازم است.',
            'parent.required' => 'فیلم مرتبط را انتخاب کنید',
            'extension.required' => 'پسوند فایل لازم است',
            'type.numeric' => 'نوع فیلم عدد می باشد',
            'parent.numeric' => 'فیلم مرتبط باید عدد باشد',
            'film.file' => 'نوع فایل آپلودی باید فایل باشد',
            'film.mimetypes' => 'نوع فایل فیلم باید ویدئو باشد',
            'poster.file' => 'نوع پوستر باید فایل باشد',
            'poster.mimes' => 'نوع پوستر باید jpg باشد',
            'poster.dimensions' => 'پوستر باید 750 در 1000 باشد',
            'priority.required_if' => 'شماره قسمت الزامی است',
        );

        $validator = Validator::make($request->all(), [
            'english_name' => 'required',
            'persian_name' => 'required',
            'type' => 'required|numeric',
            'parent' => 'required|numeric',
            'extension' => 'required',
            'priority' => 'required_if:type,3,4,5',
            'film' => 'file|mimetypes:video/*',
            'poster' => 'file|mimes:jpg|dimensions:min_width=750,min_height=1000,max_width=750,max_height=1000'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " افزودن فیلم شکست خورد",
            ], 400);
        }

        $film = new Film();
        $film->english_name = $request->english_name;
        $film->persian_name = $request->persian_name;
        $film->type = $request->type;
        $film->parent = $request->parent;
        $film->extension = $request->extension;
        $film->description = $request->description;
        $film->persian_subtitle = $request->persian_subtitle ? 1 : 0;
        $film->status = $request->status ? 1 : 0;
        if (in_array($request->type, [3,4,5])) {
            $film->priority = $request->priority;
        } else {
            $film->priority = null;
        }
        $film->save();

        if ($request->hasFile('film')) {
            $this->uploadFileById($request->film,"films", $film->id);
        }
        if ($request->hasFile('poster')) {
            $this->uploadFileById($request->poster,"films_banner", $film->id);
        }

        return response()->json([
            'data' => $film,
            'errors' => null,
            'message' => "فیلم با موفقیت اضافه شد"
        ]);
    }

    public function filmUpdate(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه فیلم نمی تواند خالی باشد',
            'id.numeric' => 'شناسه فیلم باید فقط شامل عدد باشد',
            'english_name.required' => 'عنوان انگلیسی فیلم اجباری است',
            'persian_name.required' => 'عنوان فارسی فیلم اجباری است',
            'type.required' => 'نوع فیلم لازم است.',
            'parent.required' => 'فیلم مرتبط لازم است',
            'type.numeric' => 'نوع فیلم عدد می باشد',
            'parent.numeric' => 'فیلم مرتبط باید عدد باشد',
            'film.file' => 'نوع فایل آپلودی باید فایل باشد',
            'film.mimeTypes' => 'نوع فایل فیلم باید ویدئو باشد',
            'poster.file' => 'نوع پوستر باید فایل باشد',
            'poster.mimes' => 'نوع پوستر باید jpg باشد',
            'poster.dimensions' => 'پوستر باید 750 در 1000 باشد',
            'extension.required' => 'پسوند فایل لازم است',
            'priority.required_if' => 'شماره قسمت الزامی است',
        );

        $validator = Validator::make($request->all(), [
            'id'=> 'required|numeric',
            'english_name' => 'required',
            'persian_name' => 'required',
            'type' => 'required|numeric',
            'parent' => 'required|numeric',
            'film' => 'file|mimeTypes:video',
            'extension' => 'required',
            'priority' => 'required_if:type,3,4,5',
            'poster' => 'file|mimes:jpg|dimensions:min_width=750,min_height=1000,max_width=750,max_height=1000'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " ویرایش فیلم شکست خورد",
            ], 400);
        }

        $film = $this->getFilmById($request->id);
        if(!$film){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "این فیلم وجود ندارد برای به روز رسانی"
            ]);
        }

        $film->english_name = $request->english_name;
        $film->persian_name = $request->persian_name;
        $film->type = $request->type;
        $film->parent = $request->parent;
        $film->extension = $request->extension;
        $film->description = $request->description;
        $film->persian_subtitle = $request->persian_subtitle ? 1 : 0;
        $film->status = $request->status ? 1 : 0;
        if (in_array($request->type, [3,4,5])) {
            $film->priority = $request->priority;
        } else {
            $film->priority = null;
        }
        $film->save();

        if ($request->hasFile('film')) {
            $this->uploadFileById($request->film,"films", $film->id);
        }

        if ($request->hasFile('poster')) {
            $this->uploadFileById($request->poster,"films_banner", $film->id);
        }

        return response()->json([
            'data' => $film,
            'errors' => null,
            'message' => "فیلم با موفقیت ویرایش شد"
        ]);
    }

    public function getFilmByIdRequest(Request $request)
    {
        $id = $request->id;
        $film = $this->getFilmById($id);

        $film->parent_id = null;

        if($film->type == 5){
            $parent = Film::where('id', $film->parent)->first();
            $series = Film::where('id', $parent->parent)->first();
            $film->parent_id = $series->id;
        }

        return response()->json([
            'data' => $film,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }
}

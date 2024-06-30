<?php

namespace App\Http\Controllers;

use App\Models\Film;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FilmController extends Controller
{
    public function getList(Request $request)
    {
        $search_key = $request->search_text;

        $films = Film::orderBy('id', "DESC")->whereIn('type', [ 1, 2]);
        $films = $films->where(function ($query) use ($search_key) {
            $query->where('english_name', 'like', '%' . $search_key . '%')
                ->orWhere('persian_name', 'like', '%' . $search_key . '%');
        })->paginate(25);

        return response()->json([
            'data' => $films,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getData(Request $request)
    {
        $message = array(
            'id.required' => 'id الزامی است',
            'id.numeric' => 'id باید عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $message);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن اطلاعات شکست خورد"
            ];
            return response()->json($arr, 400);
        }

        $film = Film::where('id', $request->id)->first();
        if (!$film) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "فیلم یافت نشد."
            ], 400);
        }

        $films = Film::orderBy('id', "ASC")->where('parent', $request->id)->whereIn('type', [3, 4])->get();

        $film['items'] = $films;
        $film['items_count'] = $films->count();

        return response()->json([
            'data' => $film,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function getChildById(Request $request)
    {
        $message = array(
            'id.required' => 'id الزامی است',
            'id.numeric' => 'id باید عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $message);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن اطلاعات شکست خورد"
            ];
            return response()->json($arr, 400);
        }

        $films = Film::orderBy('id', "ASC")->where('parent', $request->id)->whereIn('type', [3, 4])->get();
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


}

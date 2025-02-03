<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Models\Film;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FilmController extends Controller
{
    public function getList(Request $request)
    {
        $search_key = $request->search_text;

        $films = Film::orderBy('id', "DESC")->whereIn('type', [1, 2])->where('status' , 1);
        $films = $films->where(function ($query) use ($search_key) {
            $query->where('english_name', 'like', '%' . $search_key . '%')
                ->orWhere('persian_name', 'like', '%' . $search_key . '%');
        })->paginate(24);

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
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن اطلاعات شکست خورد"
            ], 400);
        }

        $film = Film::where('id', $request->id)->where('status' , 1)->first();
        if (!$film) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "فیلم یافت نشد."
            ], 400);
        }

        $films = Film::orderBy('priority', "ASC")->where('parent', $request->id)->whereIn('type', [3, 4, 5])->where('status' , 1)->get();

        $film['items'] = $films;
        $film['items_count'] = $films->count();
        $film['texts'] = [];

        if (((new UserHelper())->isUserSubscriptionValid($request->header("ApiToken")) && $request->with_text) || $film->permission_type === 'free') {
            $film['texts'] = $film->texts()->orderBy("start_time")->get();
        }

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
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "گرفتن اطلاعات شکست خورد"
            ], 400);
        }

        $films = Film::orderBy('id', "ASC")->where('parent', $request->id)->whereIn('type', [3, 4])->where('status' , 1)->get();

        return response()->json([
            'data' => $films,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }
}

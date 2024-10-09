<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Models\Film;
use App\Models\FilmText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FilmTextController extends Controller
{
    public function getTextList(Request $request)
    {
        $messages = array(
            'id_film.required' => 'film_id نمی تواند خالی باشد',
            'id_film.numeric' => 'film_id باید فقط شامل عدد باشد',
            'page.required' => 'page نمی تواند خالی باشد',
            'page.numeric' => 'page باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id_film' => 'required|numeric',
            'page' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "'گرفتن متن ها شکست خورد",
            ], 400);
        }

        if ((new UserHelper())->isUserSubscriptionValid($request->header("ApiToken"))) {

            $films = FilmText::where('film_id', '=', $request->id_film)->orderBy("id")->skip($request->page * 25)->take(25)->get();

            return response()->json([
                'data' => $films,
                'errors' => null,
                'message' => "اطلاعات با موفقیت گرفته شد",
            ]);

        } else {
            return response()->json([
                'data' => null,
                'errors' => null,
                'status' => 1000,
                'message' => "اشتراک شما به پایان رسیده است لطفا اشتراک خود را تمدید کنید",
            ], 400);
        }
    }

    public function getFilmTexts(Request $request)
    {
        $messages = array(
            'film_id.required' => 'شناسه فیلم نمی تواند خالی باشد',
            'film_id.numeric' => 'شناسه فیلم باید فقط شامل عدد باشد',
            'page.required' => 'شماره صفحه نمی تواند خالی باشد',
            'page.numeric' => 'شماره صفحه باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'film_id' => 'required|numeric',
            'page' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "'گرفتن متن ها شکست خورد",
            ], 422);
        }
        $film_id = $request->input('film_id');

        $film = Film::where('id' , $film_id)->where('status' , 1)->first();
        if (!$film) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "فیلم یافت نشد."
            ], 400);
        }

        if ((new UserHelper())->isUserSubscriptionValid($request->header("ApiToken"))) {

            $texts = FilmText::where('film_id', '=', $film_id)->orderBy("id")->paginate(50);
            return response()->json([
                'data' => [
                    'film' => $film,
                    'texts' => $texts,
                ],
                'errors' => null,
                'message' => "اطلاعات با موفقیت گرفته شد",
            ]);

        } else {
            return response()->json([
                'data' => null,
                'errors' => null,
                'status' => 1000,
                'message' => "اشتراک شما به پایان رسیده است لطفا اشتراک خود را تمدید کنید",
            ], 400);
        }
    }

    public function getTimesForPagin(Request $request)
    {
        $id_film = $request->id_film;
        $films = FilmText::where('film_id', '=', $id_film)->orderBy("id")->get()->toArray();

        if (!count($films)) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "لیست متن فیلم خالی است.",
            ], 400);
        }

        $chunked = array_chunk($films, 25);
        $result = [];

        foreach ($chunked as $item) {
            if (count($item)) {
                $result[] = [
                    "start_time" => $item[0]["start_time"],
                    "end_time" => end($item)["end_time"]
                ];
            }
        }

        return response()->json([
            'data' => $result,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Models\FilmText;
use App\Http\Requests\StoreFilmTextRequest;
use App\Http\Requests\UpdateFilmTextRequest;
use App\Models\Text;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FilmTextController extends Controller
{
    public function getTextList(Request $request)
    {
        $messsages = array(
            'id_film.required' => 'id_film نمی تواند خالی باشد',
            'id_film.numeric' => 'id_film باید فقط شامل عدد باشد',
            'page.required' => 'page نمی تواند خالی باشد',
            'page.numeric' => 'page باید فقط شامل عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id_film' => 'required|numeric',
            'page' => 'required|numeric',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "'گرفتن متن ها شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        if (UserController::isUserSubscriptionValid($request)) {

            $id_film = $request->id_film;
            $films = FilmText::where('id_film', '=', $id_film)->orderBy("id")->skip($request->page * 25)->take(25)->get();//1


            $arr = [
                'data' => $films,
                'errors' => null,
                'message' => "اطلاعات با موفقیت گرفته شد",
            ];
            return response()->json($arr, 200);

        } else {
            $arr = [
                'data' => null,
                'errors' => null,
                'status' => 1000, // subscription end
                'message' => "اشتراک شما به پایان رسیده است لطفا اشتراک خود را تمدید کنید",
            ];
            return response()->json($arr, 400);
        }
    }


    private function getStartTime(string $request)
    {
        $hour = (int) substr($request, 0, 2);
        $min = (int)  substr($request , 3, 2 );
        $second = (int)  substr($request, 6, 2);
        $milli =(int)  substr($request, 9, 3);

        $compute = $milli + ($second * 1000) + ($min * 60 * 1000) + ($hour * 60 * 60 * 1000);
        return $compute;
    }

    private function getEndTime(string $request)
    {
        $hour = (int)  substr($request, 17, 2);
        $min = (int) substr($request , 20, 2 );
        $second =(int)  substr($request, 23, 2);
        $milli = (int) substr($request, 26, 3);

        $compute = $milli + ($second * 1000) + ($min * 60 * 1000) + ($hour * 60 * 60 * 1000);

        return $compute;
    }

    public function insertListTexts(Request $request)
    {
        $messsages = array(
            'film_id.required' => 'film_id نمی تواند خالی باشد',
            'film_id.numeric' => 'film_id باید فقط شامل عدد باشد',
            'texts.required' => 'texts نمی تواند خالی باشد',
            'texts.array' => 'texts باید آرایه باشد',
            'start_end_time.required' => 'start_end_time ضروری است',

        );

        $validator = Validator::make($request->all(), [
            'film_id' => 'required|numeric',
            'texts' => 'required|array',
            'texts.*.text_english' => 'required',
            'texts.*.text_persian' => 'required',
            'texts.*.start_end_time' => 'required',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن متن ها شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $filmId = $request->film_id;
        $isFilmExist = FilmController::getFilmById($filmId);

        if ($isFilmExist) {

            foreach ($request->texts as $item) {
                $textEnglish = $item["text_english"];
                $textPersian = $item["text_persian"];
                $startEndTime = $item["start_end_time"];

                $text = new FilmText();
                $text->text_english = $textEnglish;
                $text->text_persian = $textPersian;
                $text->start_end_time = $startEndTime;
                $text->id_film = $filmId;
                $text->save();
            }

            $this->add10LastRowNull();
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "متن ها با موفقیت اضافه شدند",
            ];
            return response()->json($arr, 200);

        } else {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "در ابتدا فیلم را اضافه کنید",
            ];
            return response()->json($arr, 400);
        }
    }

    public function getTimesForPagin(Request $request)
    {
        $id_film = $request->id_film;
        $films = FilmText::where('id_film', '=', $id_film)->orderBy("id")->get();//1

        if (!count($films)) {
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "لیست متن فیلم خالی است.",
            ], 400);
        }

        $ok = [];
        $x = [];
        foreach ($films as $index => $film){
            // $film->start_time = $this->getStartTime($film->start_end_time);
            // $film->end_time = $this->getEndTime($film->start_end_time);
            if($index % 25 == 0){
                $start_time = $film->start_time;
            }
            if($index % 25 == 24){
                $end_time= $film->end_time;
            }
            if(isset($start_time) && isset($end_time) ){
                $x["start_time"] = $start_time;
                $x["end_time"] = $end_time;
                $ok[$index / 25 ] = $x;
                $x = null;
                $start_time = null;
                $end_time = null;
            }
        }

        $size = $films->count();
        $remain = $size % 25;
        $x["start_time"] = $films[$size - $remain + 1]['start_time'];
        $x["end_time"] =  $films[$size - 1]['end_time'];
        $ok[$size / 25] = $x;

        $arr = [
            'data' => $ok,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($arr, 200);
    }

    public function add10LastRowNull()
    {
        for ($i = 0; $i < 10; $i++) {
            $text = new FilmText();
            $text->text_english = "";
            $text->text_persian = "";
            $text->start_time = 0;
            $text->end_time = 0;
            $text->id_film = 0;
            $text->save();
        }
    }
}

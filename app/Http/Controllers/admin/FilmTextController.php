<?php

namespace App\Http\Controllers\admin;

use App\Models\FilmText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class FilmTextController extends Controller
{

    public function getTextList(Request $request)
    {

        $id_film = $request->id_film;
        $films = FilmText::where('id_film', '=', $id_film)->orderBy("id")->get();

        foreach ($films as $film){
            $film->start_time = $this->getStartTime($film->start_end_time);
            $film->end_time = $this->getEndTime($film->start_end_time);
        }


        $arr = [
            'data' => $films,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($arr, 200);

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

    //str::limit



    public function insertListTexts(Request $request)
    {
        // validation music_id is exist

        $messsages = array(
            'film_id.required' => 'film_id نمی تواند خالی باشد',
            'film_id.numeric' => 'film_id باید فقط شامل عدد باشد',
            'texts.required' => 'texts نمی تواند خالی باشد',
            'texts.array' => 'texts باید آرایه باشد',

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
       $comments = $item["comments"];
       
                $text = new FilmText();
                $text->text_english = $textEnglish;
                $text->text_persian = $textPersian;
                $text->start_end_time = $startEndTime;
                      $text->comments = $comments;
                $text->id_film = $filmId;
                $text->save();




            }

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

    public function updateListTexts(Request $request)
    {

        $messsages = array(
            'film_id.required' => 'film_id نمی تواند خالی باشد',
            'film_id.numeric' => 'film_id باید فقط شامل عدد باشد',
            'texts.required' => 'texts نمی تواند خالی باشد',
            'texts.array' => 'texts باید آرایه باشد'
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


            if (!$this->deleteListTexts($request)) {
                $arr = [
                    'data' => null,
                    'errors' => null,
                    'message' => "حذف  متن ها جهت افزودن متن جدید شکست خورد",
                ];
                return response()->json($arr, 400);
            }

            foreach ($request->texts as $item) {
                $textEnglish = $item["text_english"];
                $textPersian = $item["text_persian"];
                $startEndTime = $item["start_end_time"];
                if(isset($item["comments"]))
                $comments = $item["comments"];
                else
                $comments = "";

                $text = new FilmText();
                $text->text_english = $textEnglish;
                $text->text_persian = $textPersian;
                $text->start_end_time = $startEndTime;
                $text->comments = $comments;
                $text->id_film = $filmId;
                $text->save();


            }

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



    public function deleteListTexts(Request $request): bool
    {
        // validation music_id is exist


        $listTexts = $this->getAllTextMusic($request->film_id);

        foreach ($listTexts as $item) {
            $item->delete();
        }
        return true;
    }

    private function getAllTextMusic($film_id)
    {
        $texts = FilmText::where('id_film', "=", $film_id)->get();
        return $texts;
    }
}

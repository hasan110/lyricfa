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
        $films = FilmText::where('film_id', '=', $id_film)->orderBy("id")->get();

        $arr = [
            'data' => $films,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($arr);
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
        );

        $validator = Validator::make($request->all(), [
            'film_id' => 'required|numeric',
            'texts' => 'required|array',
            'texts.*.text_english' => 'required',
            'texts.*.start_time' => 'required',
            'texts.*.end_time' => 'required',
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
                $startTime = $item["start_time"];
                $endTime = $item["end_time"];
                $comments = $item["comments"];

                $text = new FilmText();
                $text->text_english = $textEnglish;
                $text->text_persian = $textPersian;
                $text->start_time = $startTime;
                $text->end_time = $endTime;
                $text->comments = $comments;
                $text->film_id = $filmId;
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
        $messages = array(
            'film_id.required' => 'film_id نمی تواند خالی باشد',
            'film_id.numeric' => 'film_id باید فقط شامل عدد باشد',
            'texts.required' => 'texts نمی تواند خالی باشد',
            'texts.array' => 'texts باید آرایه باشد'
        );

        $validator = Validator::make($request->all(), [
            'film_id' => 'required|numeric',
            'texts' => 'required|array',
            'texts.*.text_english' => 'required',
            'texts.*.end_time' => 'required',
            'texts.*.start_time' => 'required',
        ], $messages);

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
                $textPersian = $item["text_persian"] ?? '';
                $startTime = $item["start_time"];
                $endTime = $item["end_time"];
                $comments = $item["comments"] ?? '';

                $text = new FilmText();
                $text->text_english = $textEnglish;
                $text->text_persian = $textPersian;
                $text->start_time = $startTime;
                $text->end_time = $endTime;
                $text->comments = $comments;
                $text->film_id = $filmId;
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
        $listTexts = $this->getAllTextFilms($request->film_id);

        foreach ($listTexts as $item) {
            $item->delete();
        }
        return true;
    }

    private function getAllTextFilms($film_id)
    {
        $texts = FilmText::where('film_id', "=", $film_id)->get();
        return $texts;
    }

    public function uploadFileGetInfoAndSave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'lyrics' => 'required|file|max:512'
        ], array(
            'id.required' => 'شناسه ی آهنگ الزامی است',
            'id.numeric' => 'شناسه ی آهنگ باید عدد باشد',
            'lyrics.required' => 'فایل متنی الزامی می باشد',
            'lyrics.file' => 'نوع فایل باید فایل باشد',
            'lyrics.max' => 'ماکزیمم سایز یک مگا بایت',
        ));

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " افزودن متن شکست خورد",
            ], 400);
        }

        if ($request->hasFile('lyrics')) {
            $this->uploadFileById($request->lyrics, "film_texts", $request->id);
        }
        $just_english = $request->has('just_english') && intval($request->just_english);

        $contents = file_get_contents('https://dl.lyricfa.app/uploads/film_texts/' . $request->id . '.srt');

        $content = explode(PHP_EOL, $contents);

        $mainList = array();
        if ($just_english) {
            $list = [];
            $chunked = [];
            foreach ($content as $index => $item) {
                $item = str_replace("\r", '', $item);
                if (strlen($item) > 0) {
                    $chunked[] = $item;
                } else {
                    $list[] = $chunked;
                    $chunked = [];
                }
            }
            foreach ($list as $value) {
                $object = array(
                    "text_english" => "",
                    "text_persian" => "",
                    "start_time" => "",
                    "end_time" => "",
                );
                foreach ($value as $key => $data) {
                    if ($key === 0) continue;
                    if ($key === 1) {
                        $object["start_time"] = $this->getStartTime($data);
                        $object["end_time"] = $this->getEndTime($data);
                    } else  {
                        $object["text_english"] .= strip_tags($data).PHP_EOL;
                    }
                }
                if ($object["text_english"] == "") {
                    continue;
                }
                $mainList[] = $object;
            }
        } else {
            foreach ($content as $key => $value) {
                switch ($key) {
                    case $key % 5 == 1 :

                        $object = array(
                            "text_english" => "",
                            "text_persian" => "",
                            "start_time" => "",
                            "end_time" => "",
                        );

                        $object["start_time"] = $this->getStartTime($value);
                        $object["end_time"] = $this->getEndTime($value);
                        break;
                    case $key % 5 == 2 :
                        $object["text_english"] = $value;
                        break;
                    case $key % 5 == 3 :
                        $object["text_persian"] = $value;
                        break;
                    case $key % 5 == 4 :
                        // $object = $value;
                        break;
                    case $key % 5 == 0 :
                        // $object = $value;
                        if ($object["text_english"] != "")
                            $mainList[] = $object;
                        break;
                }
            }
        }

        $request->film_id = $request->id;
        $this->deleteListTexts($request);
        $mainList = mb_convert_encoding($mainList , 'UTF-8', 'UTF-8');
        $this->textsCreateForUpload($mainList, $request->id);
        return $mainList;
    }
    public function textsCreateForUpload($texts, $filmId)
    {
        $isFilmExist = FilmController::getFilmById($filmId);
        if ($isFilmExist) {
            foreach ($texts as $index => $item) {
                $textEnglish = $item["text_english"];
                $textPersian = $item["text_persian"];
                $startTime = $item["start_time"];
                $endTime = $item["end_time"];

                $text = new FilmText();
                $text->text_english = $textEnglish;
                $text->text_persian = $textPersian;
                $text->start_time = $startTime;
                $text->end_time = $endTime;
                $text->priority = $index + 1;
                $text->film_id = $filmId;
                $text->save();
            }
        }
    }

    public function downloadFilmTextFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], array(
            'id.required' => 'شناسه ی فیلم الزامی است',
            'id.numeric' => 'شناسه ی فیلم باید عدد باشد'
        ));

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " افزودن متن شکست خورد",
            ], 400);
        }

        $film_texts = FilmText::where('film_id',$request->id)->orderBy("id")->get();

        $file_texts = '';
        foreach ($film_texts as $key => $text)
        {
            $file_texts .= $key+1 . PHP_EOL;
            $start = $this->formatMilliseconds($text->start_time);
            $end = $this->formatMilliseconds($text->end_time);
            $file_texts .= $start.' --> '.$end . PHP_EOL;
            $file_texts .= $text->text_english . PHP_EOL;
            $file_texts .= $text->text_persian . PHP_EOL . PHP_EOL;
        }

        return $file_texts;
    }

    public function formatMilliseconds($milliseconds) {
        $seconds = floor($milliseconds / 1000);
        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);
        $milliseconds = $milliseconds % 1000;
        $seconds = $seconds % 60;
        $minutes = $minutes % 60;

        $format = '%02u:%02u:%02u,%03u';
        $time = sprintf($format, $hours, $minutes, $seconds, $milliseconds);
        return trim($time);
    }
}

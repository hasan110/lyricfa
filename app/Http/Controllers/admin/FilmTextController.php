<?php

namespace App\Http\Controllers\admin;

use App\Http\Helpers\FilmHelper;
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

        return response()->json([
            'data' => $films,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function insertListTexts(Request $request)
    {
        $messages = array(
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
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن متن ها شکست خورد",
            ], 400);
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

            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "متن ها با موفقیت اضافه شدند",
            ]);

        } else {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "در ابتدا فیلم را اضافه کنید",
            ], 400);
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
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن متن ها شکست خورد",
            ], 400);
        }

        $filmId = $request->film_id;
        $isFilmExist = FilmController::getFilmById($filmId);

        if ($isFilmExist) {

            if (!$this->deleteListTexts($request)) {
                return response()->json([
                    'data' => null,
                    'errors' => null,
                    'message' => "حذف  متن ها جهت افزودن متن جدید شکست خورد",
                ], 400);
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

            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "متن ها با موفقیت اضافه شدند",
            ]);

        } else {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "در ابتدا فیلم را اضافه کنید",
            ], 400);
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

        $upload_path = $this->uploadFile($request->lyrics, "film_texts");
        $just_english = $request->has('just_english') && intval($request->just_english);

        $contents = file_get_contents(config('app.files_base_path').$upload_path);

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
                        $object["start_time"] = (new FilmHelper())->getStartTime($data);
                        $object["end_time"] = (new FilmHelper())->getEndTime($data);
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
            $separated_blocks = [];
            $new_block = [];
            foreach ($content as $block) {
                if (strlen(trim($block)) === 0) {
                    $separated_blocks[] = $new_block;
                    $new_block = [];
                    continue;
                }
                $new_block[] = $block;
            }

            $english_characters = ['a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
            $persian_characters = ['آ','ا','ب','پ','ت','ث','ج','چ','ح','خ','د','ذ','ر','ز','ژ','س','ش','ص','ض','ط','ظ','ع','غ','ف','ق','ک','گ','ل','م','ن','و','ه','ی'];

            foreach ($separated_blocks as $key => $block) {
                $object = array(
                    "text_english" => "",
                    "text_persian" => "",
                    "start_time" => "",
                    "end_time" => "",
                );
                foreach ($block as $block_key => $item) {
                    if ($block_key === 0) continue;
                    if ($block_key === 1) {
                         $object["start_time"] = (new FilmHelper())->getStartTime($item);
                         $object["end_time"] = (new FilmHelper())->getEndTime($item);
                        continue;
                    }
                    $array = str_split($item);

                    $english_count = 0;
                    $persian_count = 0;
                    foreach($array as $letter) {
                        if (in_array($letter , $english_characters)) {
                            $english_count++;
                        } else if (in_array($letter , $persian_characters)) {
                            $persian_count++;
                        }
                    }

                    if($english_count > $persian_count) {
                        if (strlen($object["text_english"])) {
                            $object["text_english"] = $object["text_english"] . "\n" . $item;
                        } else {
                            $object["text_english"] = $item;
                        }
                    } else {
                        if (strlen($object["text_persian"])) {
                            $object["text_persian"] = $object["text_persian"] . "\n" . $item;
                        } else {
                            $object["text_persian"] = $item;
                        }
                    }

                }
                if ($object["start_time"]) {
                    $mainList[] = $object;
                }
            }
        }

        $this->deleteFile($upload_path);

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

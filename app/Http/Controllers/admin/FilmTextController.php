<?php

namespace App\Http\Controllers\admin;

use App\Http\Helpers\FilmHelper;
use App\Models\Film;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class FilmTextController extends Controller
{
    public function getTextList(Request $request)
    {
        $film = Film::where('id', $request->id_film)->first();
        if (!$film) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "فیلم یافت نشد."
            ], 400);
        }
        $texts = $film->texts()->orderBy("start_time")->get();

        return response()->json([
            'data' => $texts,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
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
            'texts.*.id' => 'required',
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

        $film = Film::where('id', $request->film_id)->first();
        if (!$film) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "فیلم یافت نشد."
            ], 400);
        }

        foreach ($request->input('texts') as $item) {
            $film->texts()->where('id' , $item["id"])->update([
                'text_english' => $item['text_english'],
                'text_persian' => $item['text_persian'] ?? '',
                'start_time' => $item['start_time'],
                'end_time' => $item['end_time'],
            ]);
        }

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "متن ها با موفقیت اضافه شدند",
        ]);
    }

    public function uploadFileGetInfoAndSave(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'lyrics' => 'required|file',
            'persian_lyrics' => 'file|required_if:persian_subtitle,1'
        ], array(
            'id.required' => 'شناسه ی آهنگ الزامی است',
            'id.numeric' => 'شناسه ی آهنگ باید عدد باشد',
            'lyrics.required' => 'فایل متنی الزامی می باشد',
            'lyrics.file' => 'نوع زیرنویس باید فایل باشد',
            'persian_lyrics.file' => 'نوع زیرنویس فارسی باید فایل باشد',
            'persian_lyrics.required_if' => 'انتخاب زیرنویس فارسی الزامی می باشد',
            'lyrics.max' => 'ماکزیمم سایز یک مگا بایت',
        ));

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " افزودن متن شکست خورد",
            ], 400);
        }

        $film = Film::where('id', $request->id)->first();
        if (!$film) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "فیلم یافت نشد."
            ], 400);
        }

        $upload_path = $this->uploadFile($request->lyrics, "film_texts");
        $persian_subtitle = $request->has('persian_subtitle') && intval($request->persian_subtitle);

        $contents = file_get_contents(config('app.files_base_path').$upload_path);

        $content = explode(PHP_EOL, $contents);

        $mainList = array();
        if ($persian_subtitle) {
            if ($request->hasFile('persian_lyrics')) {
                $persian_upload_path = $this->uploadFile($request->persian_lyrics, "film_texts");
                $persian_contents = file_get_contents(config('app.files_base_path').$persian_upload_path);
                $persian_content = explode(PHP_EOL, $persian_contents);

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

                $persian_separated_blocks = [];
                $persian_new_block = [];
                foreach ($persian_content as $persian_block) {
                    if (strlen(trim($persian_block)) === 0 && count($persian_new_block) > 0) {
                        $persian_separated_blocks[] = $persian_new_block;
                        $persian_new_block = [];
                        continue;
                    }
                    $persian_new_block[] = $persian_block;
                }

                $text_persian_list = [];
                foreach ($persian_separated_blocks as $key => $persian_block) {
                    $text_persian = '';
                    foreach ($persian_block as $block_key => $item) {
                        if ($block_key === 0 || $block_key === 1 || strlen(trim($item)) === 0) continue;
                        if (strlen($text_persian)) {
                            $text_persian .= "\n".$item;
                        } else {
                            $text_persian = $item;
                        }
                    }
                    $text_persian_list[] = $text_persian;
                }

                foreach ($separated_blocks as $key => $block) {
                    $object = array(
                        "text_english" => "",
                        "text_persian" => "",
                        "start_time" => "",
                        "end_time" => "",
                    );
                    if (isset($text_persian_list[$key])) {
                        $object["text_persian"] = $text_persian_list[$key];
                    }
                    $text_english = '';
                    foreach ($block as $block_key => $item) {
                        if ($block_key === 0) continue;
                        if ($block_key === 1) {
                            $object["start_time"] = (new FilmHelper())->getStartTime($item);
                            $object["end_time"] = (new FilmHelper())->getEndTime($item);
                            continue;
                        }
                        if (strlen($text_english)) {
                            $text_english .= "\n".$item;
                        } else {
                            $text_english = $item;
                        }
                    }
                    $object["text_english"] = $text_english;
                    if ($object["start_time"]) {
                        $mainList[] = $object;
                    }
                }

                $this->deleteFile($persian_upload_path);
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
        $mainList = mb_convert_encoding($mainList , 'UTF-8', 'UTF-8');
        $film->texts()->delete();
        $film->texts()->insert($mainList);

        return response()->json([
            'data' => $mainList,
            'errors' => [],
            'message' => "متن با موفقیت اضافه شد",
        ]);
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

        $film = Film::find($request->id);
        if (!$film) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "فیلم یافت نشد."
            ], 400);
        }

        $film_texts = $film->texts()->orderBy("start_time")->get();

        $file_texts = '';
        foreach ($film_texts as $key => $text)
        {
            $file_texts .= $key+1 . PHP_EOL;
            $start = $this->formatMilliSeconds($text->start_time);
            $end = $this->formatMilliSeconds($text->end_time);
            $file_texts .= $start.' --> '.$end . PHP_EOL;
            $file_texts .= $text->text_english . PHP_EOL;
            $file_texts .= $text->text_persian . PHP_EOL . PHP_EOL;
        }

        return $file_texts;
    }

    public function formatMilliSeconds($milliseconds): string
    {
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

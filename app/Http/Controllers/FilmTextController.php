<?php

namespace App\Http\Controllers;

use App\Http\Helpers\FilmHelper;
use App\Http\Helpers\UserHelper;
use App\Models\Film;
use App\Models\FilmText;
use App\Models\ReplaceRule;
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

    public function proccessText(Request $request)
    {
        $messages = array(
            'subtitle_file.required' => 'فایل زیرنویس وارد نشده است.',
            'subtitle_file.file' => 'زیرنویس باید بصورت فایل باشد.',
            'subtitle_file.mimetypes' => 'نوع فایل زیرنویس باید srt باشد',
            'subtitle_file.max' => 'حجم فایل باید زیر یک مگابایت باشد',
        );

        $validator = Validator::make($request->all(), [
            'subtitle_file' => 'required|file|max:1000'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "پردازش زیر نویس به مشکل خورد",
            ], 422);
        }

        $mime_file = $request->file('subtitle_file')->getClientMimeType();
        $ext_file = $request->file('subtitle_file')->getClientOriginalExtension();
        if (!in_array($mime_file , ['application/x-subrip','application/octet-stream']) || $ext_file !== 'srt') {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "فایل انتخاب شده معتبر نیست؛ لطفا فایل srt (زیرنویس) انتخاب نمایید",
            ], 400);
        }

        $mainList = array();
        $upload_path = $this->uploadFile($request->file('subtitle_file'), "film_texts");

        $contents = file_get_contents(config('app.files_base_path').$upload_path);
        $contents = strip_tags($contents);
        $content = explode(PHP_EOL, mb_convert_encoding($contents, 'UTF-8', 'UTF-8'));

        $replace_rules = ReplaceRule::where('apply_on' , 'english_text')->orderBy('priority')->get();

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

        foreach ($separated_blocks as $block) {
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
                        $object["text_english"] = $object["text_english"] . "\n" . (new FilmHelper())->replaceText($item , $replace_rules);
                    } else {
                        $object["text_english"] = (new FilmHelper())->replaceText($item , $replace_rules);
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

        $this->deleteFile($upload_path);

        return response()->json([
            'data' => $mainList,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }
}

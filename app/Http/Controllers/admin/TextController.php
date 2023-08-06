<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\MusicController;
use App\Http\Controllers\UserController;
use App\Models\Text;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;

class TextController extends Controller
{

    public function textsList(Request $request)
    {

            $id_music = $request->id_music;
            $users = Text::where('id_music', '=', $id_music)->orderBy("id")->get();
            $arr = [
                'data' => $users,
                'errors' => null,
                'message' => "اطلاعات با موفقیت گرفته شد",
            ];
            return response()->json($arr, 200);

    }

    public function getTextIncludeWord(Request $request)
    {


        if (UserController::isUserSubscriptionValid()) {

            $word = $request->word;
            $queryText = '%' . $word . '%'; //implode('%',str_split($word));
            return Text::query()->where('text_english', 'LIKE', "%{$queryText}%")->paginate(25);

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

    public function textsCreate(Request $request)
    {
        // validation music_id is exist

        $messsages = array(
            'music_id.required' => 'music_id نمی تواند خالی باشد',
            'music_id.numeric' => 'music_id باید فقط شامل عدد باشد',
            'texts.required' => 'texts نمی تواند خالی باشد',
            'texts.array' => 'texts باید آرایه باشد',

        );

        $validator = Validator::make($request->all(), [
            'music_id' => 'required|numeric',
            'texts' => 'required|array',
            'texts.*.text_english' => 'required',
            'texts.*.text_persian' => 'required',
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

        $musicId = $request->music_id;
        $isMusicExist = MusicController::getMusicById($musicId);

        if ($isMusicExist) {

            foreach ($request->texts as  $index => $item) {
                $textEnglish = $item["text_english"];
                $textPersian = $item["text_persian"];
                $startTime = $item["start_time"];
                $endTime = $item["end_time"];
                $comments = $item["comments"];

                $text = new Text();
                $text->text_english = $textEnglish;
                $text->text_persian = $textPersian;
                $text->start_time = $startTime;
                $text->end_time = $endTime;
                if ($comments) {
                    $text->comments = $comments;
                }
                $text->priority = $index + 1;
                $text->id_Music = $musicId;
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
                'message' => "در ابتدا آهنگ را در لیست آهنگ ها اضافه کنید",
            ];
            return response()->json($arr, 400);
        }
    }


    public function textsUpdate(Request $request)
    {
        // validation music_id is exist

        $messsages = array(
            'music_id.required' => 'music_id نمی تواند خالی باشد',
            'music_id.numeric' => 'music_id باید فقط شامل عدد باشد',
            'texts.required' => 'texts نمی تواند خالی باشد',
            'texts.array' => 'texts باید آرایه باشد',

        );

        $validator = Validator::make($request->all(), [
            'music_id' => 'required|numeric',
            'texts' => 'required|array',
            'texts.*.text_english' => 'required',
            'texts.*.text_persian' => 'required',
            'texts.*.start_time' => 'required',
            'texts.*.end_time' => 'required',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "افزودن متن موزیک شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $musicId = $request->music_id;
        $isMusicExist = MusicController::getMusicById($musicId);

        if ($isMusicExist) {

            if (!$this->deleteListTexts($request)) {
                $arr = [
                    'data' => null,
                    'errors' => null,
                    'message' => "حذف  متن ها جهت افزودن متن جدید شکست خورد",
                ];
                return response()->json($arr, 400);
            }

            foreach ($request->texts as $index => $item) {
                $textEnglish = $item["text_english"];
                $textPersian = $item["text_persian"];
                $startTime = $item["start_time"];
                $endTime = $item["end_time"];

                $comments = isset($item["comments"]) ? $item["comments"] : null;


                $text = new Text();
                $text->text_english = $textEnglish;
                $text->text_persian = $textPersian;
                $text->start_time = $startTime;
                $text->end_time = $endTime;
                if ($comments) {
                    $text->comments = $comments;
                }
                $text->priority = $index + 1;
                $text->id_Music = $musicId;
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
                'message' => "در ابتدا آهنگ را در لیست آهنگ ها اضافه کنید",
            ];
            return response()->json($arr, 400);
        }
    }


    public function deleteListTexts(Request $request): bool
    {
        // validation music_id is exist


        $listTexts = $this->getAllTextMusic($request->music_id);

        foreach ($listTexts as $item) {
            $item->delete();
        }
        return true;
    }

    private function getAllTextMusic($music_id)
    {
        $texts = Text::where('id_Music', "=", $music_id)->get();
        return $texts;
    }
    
    
    public function uploadFileGetInfoAndSave(Request $request)
    {
        $messsages = array(
            'id.required' => 'شناسه ی آهنگ الزامی است',
            'id.numeric' => 'شناسه ی آهنگ باید عدد باشد',
            'lyrics.required' => 'فایل متنی الزامی می باشد',
            'lyrics.file' => 'نوع فایل باید فایل باشد',
            'lyrics.max' => 'ماکزیمم سایز یک مگا بایت',

        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'lyrics' => 'required|file|max:512'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " افزودن متن شکست خورد",
            ];
            return response()->json($arr, 400);
        }
        if ($request->hasFile('lyrics')) {
            $this->uploadFileById($request->lyrics, "lyrics", $request->id);
        }


        // $contents = file_get_contents(url('uploads/lyrics/' . $request->id . '.srt'));
        
        
         $contents = file_get_contents('https://dl.lyricfa.app/uploads/lyrics/' . $request->id . '.srt');
        
        

        $name = explode(PHP_EOL, $contents);

        $mainList = array();
        $object = array(
            "text_english" => "",
            "text_persian" => "",
            "start_time" => "",
            "end_time" => "",
        );
        foreach ($name as $key => $value) {


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
//                    $object = $value;

                    break;
                case $key % 5 == 0 :
//                    $object = $value;

                    if ($object["text_english"] != "" && $object["text_persian"]  != "")
                        $mainList[] = $object;
                    break;
            }
        }

        $request->music_id = $request->id;
        $this->deleteListTexts($request);
        $this->textsCreateForUpload($mainList, $request->id);
        return $mainList;


    }

    public function downloadMusicTextFile(Request $request)
    {
        $messsages = array(
            'id.required' => 'شناسه ی آهنگ الزامی است',
            'id.numeric' => 'شناسه ی آهنگ باید عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " افزودن متن شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $music_texts = Text::where('id_music',$request->id)->orderBy("id")->get();

        $file_texts = '';
        foreach ($music_texts as $key => $text)
        {
            $file_texts .= $key+1 . PHP_EOL;
            $start = $this->formatMilliseconds($text->start_time);
            $end = $this->formatMilliseconds($text->end_time);
            $file_texts .= $start.' --> '.$end . PHP_EOL;
            $file_texts .= $text->text_english . PHP_EOL;
            $file_texts .= $text->text_persian . PHP_EOL . PHP_EOL;
        }

//         $contents = file_get_contents('https://dl.lyricfa.app/uploads/lyrics/' . $request->id . '.srt');


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

    public function textsCreateForUpload($texts, $musicId)
    {


        $isMusicExist = MusicController::getMusicById($musicId);

        if ($isMusicExist) {

            foreach ($texts as $index => $item) {
                $textEnglish = $item["text_english"];
                $textPersian = $item["text_persian"];
                $startTime = $item["start_time"];
                $endTime = $item["end_time"];

                $text = new Text();
                $text->text_english = $textEnglish;
                $text->text_persian = $textPersian;
                $text->start_time = $startTime;
                $text->end_time = $endTime;
                $text->priority = $index + 1;
                $text->id_Music = $musicId;
                $text->save();
            }

            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "متن ها با موفقیت اضافه شدند",
            ];
            return response()->json($arr, 200);

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

}

// http://localhost/lyricfa/public/add_list_texts?music_id=4&texts[0][text_english]=yes&texts[0][start_time]=100&texts[0][text_persian]=100&texts[0][end_time]=100&texts[1][text_english]=%D8%AF%D8%AE&texts[1][start_time]=200&texts[1][text_persian]=200&texts[1][end_time]=100

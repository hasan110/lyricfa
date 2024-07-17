<?php

namespace App\Http\Controllers;

use App\Models\Text;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use phpDocumentor\Reflection\Types\True_;
use Exception;

class TextController extends Controller
{
    public function getTextList(Request $request)
    {
        if (UserController::isUserSubscriptionValid($request)) {

            $id_music = $request->id_music;
            $texts = Text::where('id_music', '=', $id_music)->orderBy("id")->get();

            $textsWithIdiom = array();
            foreach ($texts as $item){
                $textsWithIdiom[] = $item;
            }

            $arr = [
                'data' => $textsWithIdiom,
                'errors' => null,
                'message' => "اطلاعات با موفقیت گرفته شد",
            ];
            return response()->json($arr, 200);

        } else {
            $arr = [
                'data' => null,
                'errors' => null,
                'status' => 1000,
                'message' => "اشتراک شما به پایان رسیده است لطفا اشتراک خود را تمدید کنید",
            ];
            return response()->json($arr, 400);
        }
    }

    public function getTextIncludeWord(Request $request)
    {
        if (UserController::isUserSubscriptionValid()) {
            $word = $request->word;
            $queryText = '%' . $word . '%';
            return Text::query()->where('text_english', 'LIKE', "%{$queryText}%")->paginate(25);
        } else {
            $arr = [
                'data' => null,
                'errors' => null,
                'status' => 1000,
                'message' => "اشتراک شما به پایان رسیده است لطفا اشتراک خود را تمدید کنید",
            ];
            return response()->json($arr, 400);
        }
    }

    public function getBiggerId()
    {
        return Text::orderBy('id', 'DESC')->first()->id;
    }

    public function getTextById($id)
    {
        return Text::where('id', $id)->first();
    }

    public function check10LastRowIsNull()
    {
        $biggerId = $this->getBiggerId();

        if ($biggerId > 10) {
            $last_1 = $this->getTextById($biggerId);
            $last_2 = $this->getTextById($biggerId - 1);
            $last_3 = $this->getTextById($biggerId - 2);
            $last_4 = $this->getTextById($biggerId - 3);
            $last_5 = $this->getTextById($biggerId - 4);
            $last_6 = $this->getTextById($biggerId - 5);
            $last_7 = $this->getTextById($biggerId - 6);
            $last_8 = $this->getTextById($biggerId - 7);
            $last_9 = $this->getTextById($biggerId - 8);
            $last_10 = $this->getTextById($biggerId - 9);
            if (!($last_1->end_time) && !($last_2->end_time) && !($last_3->end_time) && !($last_4->end_time) && !($last_5->end_time) &&
                !($last_6->end_time) && !($last_7->end_time) && !($last_8->end_time) && !($last_9->end_time) && !($last_10->end_time)
            ) {
                return 1;
            } else {
                return 0;
            }
        } else {
            return 0;
        }
    }

    public function add10LastRowNull()
    {
        for ($i = 0; $i < 10; $i++) {
            $text = new Text();
            $text->text_english = "";
            $text->text_persian = "";
            $text->start_time = 0;
            $text->end_time = null;
            $text->id_Music = 0;
            $text->save();
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Http\Helpers\UserWordHelper;
use App\Models\Grammer;
use App\Models\UserWord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Word;
use App\Models\WordEnEn;
use App\Models\Idiom;
use App\Models\Map;

class UserWordController extends Controller
{
    function getUserWordsById(Request $request )
    {
        $words = UserWord::where('user_id', $request->user_id)->paginate(24);

        return response()->json([
            'data'=>$words,
            'errors'=>[],
            'message'=>"اطلاعات با موفقیت گرفته شد"
        ]);
    }

    function getUserWordsReviews(Request $request)
    {
        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $words = UserWord::orderBy('updated_at', "ASC")->where('user_id', $user->id);
        if (isset($request->status)) {
            $words = $words->where('status' , $request->status)->limit(50);
        }
        $words = $words->get();

        $reviews = [];
        foreach ($words as $item) {
            $t1 = Carbon::parse($item->updated_at);
            $t2 = Carbon::now();
            $diff = $t1->diff($t2);

            switch ($item->status) {
                case 0:
                    $reviews[] = $item;
                    break;
                case 1:
                    if ($diff->days >= 1) {
                        $reviews[] = $item;
                    }
                    break;
                case 2:
                    if ($diff->days >= 2) {
                        $reviews[] = $item;
                    }
                    break;
                case 3:
                    if ($diff->days >= 4) {
                        $reviews[] = $item;
                    }
                    break;
                case 4:
                    if ($diff->days >= 8) {
                        $reviews[] = $item;
                    }
                    break;
                case 5:
                    if ($diff->days >= 16) {
                        $reviews[] = $item;
                    }
                    break;
                case 6:
                default:
                    break;
            }
        }

        $response = [];
        foreach ($reviews as $itemMain) {
            $item = $itemMain->word;
            $id = $itemMain->id;
            $type = $itemMain->type;
            $comment_user = (new UserWordHelper())->getUserWordComment($user->id , $item);
            if (intval($type) === 3) {
                $grammar = Grammer::where('id' , $item)->first();
                $mainResponse = ['id' => $id, 'word' => $item, 'type' => $type, 'mean' => null, "english_mean" => null, "idioms" => null, "user_comment" => null, "grammer"=>$grammar];
            } else {
                $get_word = Word::where('english_word', $item)->first();
                if (!$get_word) {
                    $map = Map::where('word', $item)->first();

                    if (!$map) {
                        $mainResponse = ['id' => $id, 'word' => $item, 'type' => $type, 'mean' => null, "english_mean" => null, "idioms" => null, "user_comment" => $comment_user, "grammer"=>null];
                    } else {
                        $ciBase = $map->ci_base;
                        $get_base_word = Word::where('english_word', $ciBase)->first();
                        $get_english_word = WordEnEn::where('ci_word', $ciBase)->first();
                        $get_idioms = Idiom::where('base', $ciBase)->get();
                        $mainResponse = ['id' => $id, 'word' => $item, 'type' => $type, 'mean' => $get_base_word, "english_mean" => $get_english_word, "idioms" => $get_idioms, "user_comment" => $comment_user, "grammer"=>null];
                    }
                } else {
                    $get_english_word = WordEnEn::where('ci_word', $item)->first();
                    $get_idioms = Idiom::where('base', $item)->get();
                    $mainResponse = ['id' => $id, 'word' => $item, 'type' => $type, 'mean' => $get_word, "english_mean" => $get_english_word, "idioms" => $get_idioms, "user_comment" => $comment_user, "grammer"=>null];
                }
            }
            $response[] = $mainResponse;
        }

        return response()->json([
            'data' => $response,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد"
        ]);
    }

    function getLightenerBoxData(Request $request)
    {
        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));
        $box_data = [];
        for($i = 0 ; $i <= 5 ; $i++)
        {
            $count = UserWord::where('user_id', $user->id)->where('status', $i)->count();
            $words_count = UserWord::where('user_id', $user->id)->where('status', $i)->where('type', 0)->count();
            $idioms_count = UserWord::where('user_id', $user->id)->where('status', $i)->where('type', 1)->count();
            $comment_count = UserWord::where('user_id', $user->id)->where('status', $i)->where('type', 2)->count();
            $grammars_count = UserWord::where('user_id', $user->id)->where('status', $i)->where('type', 3)->count();
            $reviews_count = 0;
            $date = Carbon::now();
            switch ($i) {
                case 0:
                    $reviews_count = $count;
                    break;
                case 1:
                    $reviews_count = UserWord::where('user_id', $user->id)->where('status', $i)->where('updated_at', '<=' , $date->subDays(1))->count();
                    break;
                case 2:
                    $reviews_count = UserWord::where('user_id', $user->id)->where('status', $i)->where('updated_at', '<=' , $date->subDays(2))->count();
                    break;
                case 3:
                    $reviews_count = UserWord::where('user_id', $user->id)->where('status', $i)->where('updated_at', '<=' , $date->subDays(4))->count();
                    break;
                case 4:
                    $reviews_count = UserWord::where('user_id', $user->id)->where('status', $i)->where('updated_at', '<=' , $date->subDays(8))->count();
                    break;
                case 5:
                    $reviews_count = UserWord::where('user_id', $user->id)->where('status', $i)->where('updated_at', '<=' , $date->subDays(16))->count();
                    break;
            }

            $data = [
                'status' => $i,
                'total_count' => $count,
                'words_count' => $words_count,
                'idioms_count' => $idioms_count,
                'comments_count' => $comment_count,
                'grammars_count' => $grammars_count,
                'reviews_count' => $reviews_count,
            ];
            $box_data[$i] = $data;
        }
        return response()->json(['data' => $box_data, 'errors' => [], 'message' => "اطلاعات با موفقیت گرفته شد"]);
    }

    function insertUserWord(Request $request)
    {
        $messages = array(
            'word.required'=>'لغت الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'word' => 'required',
        ], $messages);

        if($validator->fails()){
            return response()->json([
                'data'=>null,
                'errors'=>$validator->errors(),
                'message'=>"افزودن لغت شکست خورد"
            ], 400);
        }

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $user_word = (new UserWordHelper())->getUserWord($user->id , $request->word);

        if (isset($user_word)) {
            $user_word->comment_user = $request->comment_user;
            $user_word->save();
            return response()->json([
                'data'=>$user_word,
                'errors'=>[],
                'message'=>"لغت با موفقیت به روز شد"
            ]);
        } else {
            $user_word = new UserWord;
            $user_word->user_id = $user->id;
            $user_word->word = $request->word;
            $user_word->comment_user = $request->comment_user;
            $user_word->status = 0;
            $user_word->type = $request->type ?? 0;
            $user_word->save();

            return response()->json([
                'data'=>$user_word,
                'errors'=>[],
                'message'=>"لغت با موفقیت اضافه شد"
            ]);
        }
    }

    function editWordsUser(Request $request)
    {
        $messages = array(
            'words_not_learn.required' => 'لیست لغاتی که بلد هستید الزامی است',
            'words_learned.required' => 'لیست لغاتی که بلد نیستید الزامی است',
        );

        $validator = Validator::make($request->all(), [
            'words_learned' => 'required',
            'words_not_learn' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => null
            ], 400);
        }

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $arrWordsLearn = $request->words_learned;
        $arrWordsDont = $request->words_not_learn;

        foreach ($arrWordsLearn as $item) {
            if (!$item) continue;
            $wordInfo = (new UserWordHelper())->getUserWord($user->id, $item);
            if (isset($wordInfo)) {
                $getItem = $wordInfo;
                if((int)$getItem->status >= 5){
                    $new_status = 5;
                }else{
                    $new_status = $getItem->status + 1;
                }
                $getItem->status = $new_status;
                $getItem->save();
            }
        }

        foreach ($arrWordsDont as $item) {
            if (!$item) continue;
            $wordInfo = (new UserWordHelper())->getUserWord($user->id, $item);
            if (isset($wordInfo)) {
                $getItem = $wordInfo;
                $new_status = 0;
                $getItem->status = $new_status;
                $getItem->updated_at =  Carbon::now();
                $getItem->save();
            }
        }

        return response()->json([
            'data' => null,
            'errors' => [],
            'message' => "جعبه لایتنر با موفقیت به روز شد"
        ]);
    }

    function removeWordUser(Request $request)
    {
        $messages = array(
            'id_word_user.required' => 'آیدی لغت ضروری هست',
            'id_word_user.numeric' => 'آیدی لغت باید عدد باشد',
        );

        $validator = Validator::make($request->all(), [
            'id_word_user' => 'required|numeric'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => null
            ], 400);
        }

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));

        $user_word = UserWord::where('id' , $request->id_word_user)->first();
        if($user->id == $user_word->user_id){
            $user_word->delete();
        }
        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "لغت با موفقیت حذف شد"
        ]);
    }
}




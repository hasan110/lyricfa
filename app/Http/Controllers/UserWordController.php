<?php

namespace App\Http\Controllers;

use App\Models\Grammer;
use App\Models\User;
use App\Models\UserWord;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Word;
use App\Models\WordEnEn;
use App\Models\Idiom;

use App\Models\Map;

class UserWordController extends Controller
{
    function getUserWordsById(Request $request ){


        $words = UserWord::where('user_id', $request->user_id)->paginate(25);

        $arr = [
            'data'=>$words,
            'errors'=>[
            ],
            'message'=>"اطلاعات با موفقیت گرفته شد"
        ];
        return response()->json($arr , 200);

    }

    function getUserWordsReviews(Request $request ){

        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if($user) {
            $user_id = $user->id;

            $words = UserWord::orderBy('updated_at', "ASC")->where('user_id', $user_id);
            if(isset($request->status)){
                $words = $words->where('status' , $request->status);
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
                        break;

                    default:
                }
            }

            $response = [];
            foreach ($reviews as $itemMain) {

                $item = $itemMain->word;
                $id = $itemMain->id;
                $type = $itemMain->type;

                $comment_user = UserWordController::getCommentUser($item, $user_id );
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
            $responseCheck = ['data' => $response, 'errors' => [], 'message' => "اطلاعات با موفقیت گرفته شد"];
            return response()->json($responseCheck, 200);
        }else{
            $responseCheck = ['data' => null, 'errors' => [], 'message' => "خطا در اجراز هویت کاربر"];
            return response()->json($responseCheck, 401);
        }
    }

    function getLightenerBoxData(Request $request )
    {
        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if($user) {
            $user_id = $user->id;
            $box_data = [];
            for($i = 0 ; $i <= 5 ; $i++)
            {
                $count = UserWord::where('user_id', $user_id)->where('status', $i)->count();
                $words_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('type', 0)->count();
                $idioms_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('type', 1)->count();
                $comment_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('type', 2)->count();
                $grammars_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('type', 3)->count();
                $reviews_count = 0;
                $date = Carbon::now();
                switch ($i) {
                    case 0:
                        $reviews_count = $count;
                        break;
                    case 1:
                        $reviews_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('updated_at', '<=' , $date->subDays(1))->count();
                        break;
                    case 2:
                        $reviews_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('updated_at', '<=' , $date->subDays(2))->count();
                        break;
                    case 3:
                        $reviews_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('updated_at', '<=' , $date->subDays(4))->count();
                        break;
                    case 4:
                        $reviews_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('updated_at', '<=' , $date->subDays(8))->count();
                        break;
                    case 5:
                        $reviews_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('updated_at', '<=' , $date->subDays(16))->count();
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
            $response = ['data' => $box_data, 'errors' => [], 'message' => "اطلاعات با موفقیت گرفته شد"];
            return response()->json($response, 200);
        }else{
            $responseCheck = ['data' => null, 'errors' => [], 'message' => "خطا در اجراز هویت کاربر"];
            return response()->json($responseCheck, 401);
        }
    }

    function insertUserWord(Request $request ){
        $messsages = array(
            'word.required'=>'لغت الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'word' => 'required',
        ], $messsages);

        if($validator->fails()){
            $arr = [
                'data'=>null,
                'errors'=>$validator->errors(),
                'message'=>"افزودن لغت شکست خورد"
            ];
            return response()->json($arr, 400);
        }

        $api_token = $request->header("ApiToken");
        $user = UserController::getUserByToken($api_token);
        if($user) {
            $user_id = $user->id;
        }else{
            $arr = [
                'data'=>null,
                'errors'=>[
                ],
                'message'=>"کاربر احراز هویت نشده است"
            ];

            return response()->json($arr , 401);
        }

        $word = $this->wordIsExistInListUserWords($request);

        if(isset($word)){ // array use count
            $word->comment_user = $request->comment_user;
            $word->save();

            $arr = [
                'data'=>$word,
                'errors'=>[
                ],
                'message'=>"لغت با موفقیت به روز شد"
            ];


            return response()->json($arr , 200);
        }else{

            //insert new word
            $userWord = new UserWord;
            $userWord->user_id = (int) $user_id;
            $userWord->word = $request->word;
            $userWord->comment_user = $request->comment_user;
            $userWord->status = 0;
            $userWord->type = $request->type ?? 0;

            $userWord->save();

            $arr = [
                'data'=>$userWord,
                'errors'=>[
                ],
                'message'=>"لغت با موفقیت اضافه شد"
            ];
            return response()->json($arr , 200);
        }


    }

    function wordIsExistInListUserWords(Request $request){

        $api_token = $request->header("ApiToken");
        $user = UserController::getUserByToken($api_token);
        if($user){
          $user_id =   $user->id;
            return UserWord::where('word',$request->word)->where('user_id',$user_id)->first();
        }else{
            return  null;
        }

    }

    public static function getCommentUser($word, $user_id){

        try{
            return UserWord::where('word',$word)->where('user_id',$user_id)->first()->comment_user;
        }catch (Exception $e){
            return null;
        }


    }

    function getWordInfo($user_id, $word){

       return UserWord::where('word',$word)->where('user_id', $user_id)->first();

    }

    function editWordsUser(Request $request)
    {
        $messsages = array(
            'words_not_learn.required' => 'لیست لغاتی که بلد هستید الزامی است',
            'words_learned.required' => 'لیست لغاتی که بلد نیستید الزامی است',

        );

        $validator = Validator::make($request->all(), [
            'words_learned' => 'required',
            'words_not_learn' => 'required',
        ], $messsages);


        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => null
            ];
            return response()->json($arr, 400);
        }


        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $userId = $user->id;

            $arrWordsLearn = $request->words_learned;
            $arrWordsDont = $request->words_not_learn;

            foreach ($arrWordsLearn as $item) {
                if (!$item) continue;
                $wordInfo = $this->getWordInfo($userId, $item);
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
                $wordInfo = $this->getWordInfo($userId, $item);
                if (isset($wordInfo)) {
                    $getItem = $wordInfo;
//                    if((int)$getItem->status <= 1){
//                        $new_status = $getItem->status;
//                    }else{
//                        $new_status = $getItem->status - 1;
//                    }
                    $new_status = 0;
                    $getItem->status = $new_status;
                    $getItem->updated_at =  Carbon::now();
                    $getItem->save();
                }
            }

            $arr = [
                'data' => null,
                'errors' => [
                ],
                'message' => "جعبه لایتنر با موفقیت به روز شد"
            ];
            return response()->json($arr, 200);
        }else{
            $arr = [
                'data' => null,
                'errors' => [
                ],
                'message' => "کاربر نامعتبر است"
            ];
            return response()->json($arr, 401);
        }
    }

    function getWordById($id){

        return UserWord::where('id',$id)->first();
    }

    function removeWordUser(Request $request){
        $messsages = array(
            'id_word_user.required' => 'آیدی لغت ضروری هست',
            'id_word_user.numeric' => 'آیدی لغت باید عدد باشد',

        );

        $validator = Validator::make($request->all(), [
            'id_word_user' => 'required|numeric'
        ], $messsages);


        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => null
            ];
            return response()->json($arr, 400);
        }


        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $userId = $user->id;

            $wordUser = $this->getWordById($request->id_word_user);
            if($userId == $wordUser->user_id){
                $wordUser->delete();
                $arr = [
                    'data' => null,
                    'errors' => null,
                    'message' => "لغت با موفقیت حذف شد"
                ];
                return response()->json($arr, 200);
            }


        }else{
            $arr = [
                'data' => null,
                'errors' => [
                ],
                'message' => "کاربر نامعتبر است"
            ];
            return response()->json($arr, 401);
        }
    }
}




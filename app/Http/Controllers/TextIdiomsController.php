<?php

namespace App\Http\Controllers;

use App\Http\Controllers\IdiomController;
use App\Models\TextIdioms;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TextIdiomsController extends Controller
{


    public function getTextIdioms(Request $request)
    {


        $messsages = array(
            'id_text.required' => 'id_text نمی تواند خالی باشد',
            'id_text.numeric' => 'id_text باید عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id_text' => 'required|numeric'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " شکست در وارد کردن اطلاعات",
            ];
            return response()->json($arr, 400);
        }

        $idioms = TextIdioms::orderBy('id', 'ASC')->
        where('id_text', "$request->id_text")->get();


        $idiomsDetails = array();
        foreach ($idioms as $item) {
            $item->details = IdiomController::getIdiomById($item->id_idiom);
            $idiomsDetails[] = $item;
        }
        $response = [
            'data' => $idiomsDetails,
            'errors' => [
            ],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);
    }
    public static function getTextIdiomsById(int $id_text)
    {


        $idioms = TextIdioms::orderBy('id', 'ASC')->
        where('id_text', $id_text)->get();


        $idiomsDetails = array();
        foreach ($idioms as $item) {
            $item->details = IdiomController::getIdiomById($item->id_idiom);
            $idiomsDetails[] = $item;
        }
        return $idiomsDetails;
    }


    public function getMusicIdioms(Request $request)
    {


        $messsages = array(
            'id_music.required' => 'id_music نمی تواند خالی باشد',
            'id_music.numeric' => 'id_music باید عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id_music' => 'required|numeric'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " شکست در گرفتن اطلاعات",
            ];
            return response()->json($arr, 400);
        }


        $texts =  TextController::getMusicTextList($request->id_music);

        $idioms = array();


        foreach ($texts as $text){

        }
            $idioms = TextIdioms::orderBy('id', 'ASC')->
            where('id_text', 'LIKE', "%{$request->id_text}%")->get();

            $response = [
                'data' => $idioms,
                'errors' => [
                ],
                'message' => "اطلاعات با موفقیت گرفته شد",
            ];
            return response()->json($response, 200);
    }
//
//    public function getTextIdioms(Request $request)
//    {
//
//
//        $messsages = array(
//            'id_text.required' => 'id_text نمی تواند خالی باشد',
//            'id_text.numeric' => 'id_text باید عدد باشد',
//            'id_idiom.required' => 'id_idiom نمی تواند خالی باشد',
//            'id_idiom.numeric' => 'id_idiom باید عدد باشد',
//        );
//
//        $validator = Validator::make($request->all(), [
//            'id_text' => 'required|numeric',
//            'id_idiom' => 'required|numeric'
//        ], $messsages);
//
//        if ($validator->fails()) {
//            $arr = [
//                'data' => null,
//                'errors' => $validator->errors(),
//                'message' => " شکست در وارد کردن اطلاعات",
//            ];
//            return response()->json($arr, 400);
//        }
//
//            $idioms = TextIdioms::orderBy('id', 'ASC')->
//            where('english_name', 'LIKE', "%{$request->search_text}%")->get();
//
//            foreach ($idioms as $singer) {
//                $singer->singer = json_decode(json_encode($singer));
//
//                $num_like = LikeSingerController::getNumberSingerLike($singer->id);
//                $num_comment = CommentSingerController::getNumberSingerComment($singer->id);
//
//                $singer->num_like = $num_like;
//                $singer->num_comment = $num_comment;
//                $singer->user_like_it = LikeSingerController::isUserLike($singer->id,$user_id);
//
//                unset($singer['id'], $singer['english_name'], $singer['persian_name']);
//            }
//
//            $response = [
//                'data' => $idioms,
//                'errors' => [
//                ],
//                'message' => "اطلاعات با موفقیت گرفته شد",
//            ];
//            return response()->json($response, 200);
//    }

}

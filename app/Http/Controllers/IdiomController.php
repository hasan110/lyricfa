<?php

namespace App\Http\Controllers;

use App\Models\Idiom;
use Illuminate\Http\Request;

class IdiomController extends Controller
{
    public function getIdiomsWord(Request $request)
    {
        $word = $request->word;
        $get_idioms = Idiom::where('base', $word)->get();

        if (!$get_idioms) {
            $arr = [
                'data' => null,
                'errors' => [
                ],
                'message' => "هیچ کلمه ای پیدا نشد",
            ];
            return response()->json($arr, 200);
        } else {
            $arr = [
                'data' => $get_idioms,
                'errors' => [
                ],
                'message' => "اطلاعات با موفقیت گرفته شد",
            ];
            return response()->json($arr, 200);
        }

    }
    
        public static function getIdiomById(int $id)
    {
        $idiom =  Idiom::where('id', $id)->first();

        $rooms = json_decode($idiom->definition, true);

        $idiom->parse = $rooms;
        return $idiom;
    }


    public function searchIdiom(Request $request)
    {
        $idioms = Idiom::orderBy('id', 'DESC')->
        where('phrase', 'LIKE', "%{$request->search_text}%")->
        orWhere('definition', 'LIKE', "%{$request->search_text}%")->
        paginate(25);


        foreach ($idioms as $index => $item) {
            $rooms = json_decode($item->definition, true);

            $idioms[$index]->parse = $rooms;
        }

        $response = [
            'data' => $idioms,
            'errors' => [
            ],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);

    }

}

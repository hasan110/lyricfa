<?php

namespace App\Http\Controllers;

use App\Models\Map;
use Illuminate\Http\Request;

class MapController extends Controller
{

    function getBaseWord(Request $request){

        $word = $request->word;
        $get_word =  Map::where('word', $word)->first();
        return response()->json($get_word , 200);
    }

    function getWordMapReasons(Request $request)
    {
        $map = Map::where('word', $request->word)->with('map_reasons')->first();
        if (!$map) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "اطلاعات گرامر یافت نشد."
            ] , 404);
        }

        return response()->json([
            'data' => $map,
            'errors' => [],
            'message' => "اطلاعات با موفقیت دریافت شد."
        ]);
    }
}

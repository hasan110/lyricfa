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
}

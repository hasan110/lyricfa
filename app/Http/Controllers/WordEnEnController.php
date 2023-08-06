<?php

namespace App\Http\Controllers;

use App\Models\WordEnEn;
use App\Http\Requests\StoreWordEnEnRequest;
use App\Http\Requests\UpdateWordEnEnRequest;

use Illuminate\Http\Request;


class WordEnEnController extends Controller
{

    function getAllWords(Request $request)
    {

        $words = WordEnEn::paginate(25);

        $arr = [
            'data' => $words,
            'errors' => [
            ],
            'message' => "اطلاعات با موفقیت گرفته شد"
        ];
        return response()->json($arr, 200);
    }


    function getWord(Request $request)
    {
        $word = $request->word;
        $get_word = WordEnEn::where('ci_word', $word)->first();

        if (!$get_word) {
            $arr = [
                'data' => $get_word,
                'errors' => [
                ],
                'message' => "هیچ کلمه ای پیدا نشد"
            ];
            return response()->json($arr, 200);
        } else {
            $arr = [
                'data' => $get_word,
                'errors' => [
                ],
                'message' => "اطلاعات با موفقیت گرفته شد"
            ];
            return response()->json($arr, 200);
        }

    }
}

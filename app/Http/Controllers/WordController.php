<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Http\Helpers\UserWordHelper;
use App\Models\Word;
use App\Models\WordEnEn;
use App\Models\Idiom;
use App\Models\Map;
use Illuminate\Http\Request;

class WordController extends Controller
{
    function getWord(Request $request)
    {
        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));
        $word = $request->word;
        $comment_user = (new UserWordHelper())->getUserWordComment($user->id , $word);
        $get_word = Word::where('english_word', $word)->first();

        if (!$get_word) {
            $map = Map::where('word', $word)->first();

            if (!$map) {
                $mainResponse = ['word' => $word, 'mean' => null, "english_mean" => null, "idioms" => null, "user_comment" => $comment_user];
            } else {
                $ciBase = $map->ci_base;
                $get_base_word = Word::where('english_word', $ciBase)->first();
                $get_english_word = WordEnEn::where('ci_word', $ciBase)->first();
                $get_idioms = Idiom::where('base', $ciBase)->get();
                $mainResponse = ['word' => $word, 'mean' => $get_base_word, "english_mean" => $get_english_word, "idioms" => $get_idioms, "user_comment" => $comment_user];
            }
        } else {
            $get_english_word = WordEnEn::where('ci_word', $word)->first();
            $get_idioms = Idiom::where('base', $word)->get();
            $mainResponse = ['word' => $word, 'mean' => $get_word, "english_mean" => $get_english_word, "idioms" => $get_idioms, "user_comment" => $comment_user];
        }


        if (!$mainResponse['mean'] && !$mainResponse['english_mean'] && !$mainResponse['idioms'] && $this->charIsBigLetter($word[0])) {

            $word = strtolower($word);
            $get_word = Word::where('english_word', $word)->first();

            if (!$get_word) {
                $map = Map::where('word', $word)->first();

                if (!$map) {
                    $mainResponse = ['word' => $word, 'mean' => null, "english_mean" => null, "idioms" => null, "user_comment" => $comment_user];
                } else {
                    $ciBase = $map->ci_base;
                    $get_base_word = Word::where('english_word', $ciBase)->first();
                    $get_english_word = WordEnEn::where('ci_word', $ciBase)->first();
                    $get_idioms = Idiom::where('base', $ciBase)->get();
                    $mainResponse = ['word' => $word, 'mean' => $get_base_word, "english_mean" => $get_english_word, "idioms" => $get_idioms, "user_comment" => $comment_user];
                }
            } else {
                $get_english_word = WordEnEn::where('ci_word', $word)->first();
                $get_idioms = Idiom::where('base', $word)->get();
                $mainResponse = ['word' => $word, 'mean' => $get_word, "english_mean" => $get_english_word, "idioms" => $get_idioms, "user_comment" => $comment_user];
            }

        }
        return response()->json(['data' => $mainResponse, 'errors' => [], 'message' => "اطلاعات با موفقیت گرفته شد"]);
    }

    public function charIsBigLetter($char)
    {
        if ($char >= chr(65) && $char <= chr(90)) {
            return true;
        }
        return false;
    }

    function checkWord(Request $request)
    {
        $word = $request->word;
        if (!$word) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "لغت نمیتواند خالی باشد."
            ], 400);
        }

        $check_lower_case = Word::where('english_word' , lcfirst($word))->exists();
        $check_upper_case = Word::where('english_word' , ucfirst($word))->exists();

        if(!$check_lower_case) {
            $check_lower_case = Map::where('word' , lcfirst($word))->orWhere('ci_base' , lcfirst($word))->exists();
        }
        if(!$check_upper_case) {
            $check_upper_case = Map::where('word' , ucfirst($word))->orWhere('ci_base' , ucfirst($word))->exists();
        }

        return response()->json([
            'data' => [
                'word' => $word,
                'uppercase' => $check_upper_case,
                'lowercase' => $check_lower_case,
            ],
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد"
        ]);
    }
}

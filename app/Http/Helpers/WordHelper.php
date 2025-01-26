<?php

namespace App\Http\Helpers;

use App\Models\Word;

class WordHelper extends Helper
{
    function getWord($word)
    {
        $lower_word = strtolower($word);
        $get_word = Word::where('english_word' , $word)->first();
        if (!$get_word) {
            $get_word = Word::where('english_word' , $lower_word)->first();
            if (!$get_word) {
                $get_word = Word::where('english_word' , ucfirst($lower_word))->first();
            }
        }

        return $get_word;
    }
}

<?php

namespace App\Http\Helpers;

use App\Models\Map;

class IdiomHelper extends Helper
{
    public function convertPhraseToBase($phrase)
    {
        $phrase = $this->removeWordFromParentheses($phrase);

        $words = explode(' ', $phrase);

        $new_words = array();
        foreach ($words as $word) {
            $changed_word = trim($word);
            if (empty($changed_word)) {
                continue;
            }
            if ($word === 'something' || $word === 'someone') {
                $changed_word = '';
            }

            if (!empty($changed_word)) {
                $word_base = Map::where('word' , $changed_word)->first();
                if($word_base){
                    $changed_word = $word_base->ci_base;
                }

                $changed_word = strtolower($changed_word);
                $new_words[] = $changed_word;
            }
        }

        return implode(' ', $new_words);
    }

    public function removeWordFromParentheses($string)
    {
        if (strpos($string, '(') === false || strpos($string, ')') === false) {
            return $string;
        }

        do {
            $open = strpos($string, '(');
            $close = strpos($string, ')');
            $before = substr($string, 0, $open);
            $after = substr($string, $close + 1);
            $between = substr($string, $open, $close - $open + 1);
            $between = str_replace(['(' , ')'], ['',''] , $between);
            $between = preg_replace('/\bor\b/', '', $between);
            $string = $before.$between.$after;
            $founded = strpos($string, '(') !== false && strpos($string, ')') !== false;
        } while ($founded);

        return $string;
    }
}

<?php

namespace App\Http\Helpers;

use App\Models\Music;
use App\Models\ReplaceRule;
use App\Models\Singer;

class FilmHelper extends Helper
{
    public function getStartTime(string $time)
    {
        $hour = (int) substr($time, 0, 2);
        $min = (int)  substr($time , 3, 2 );
        $second = (int)  substr($time, 6, 2);
        $milli =(int)  substr($time, 9, 3);

        return $milli + ($second * 1000) + ($min * 60 * 1000) + ($hour * 60 * 60 * 1000);
    }

    public function getEndTime(string $time)
    {
        $hour = (int)  substr($time, 17, 2);
        $min = (int) substr($time , 20, 2 );
        $second =(int)  substr($time, 23, 2);
        $milli = (int) substr($time, 26, 3);

        return $milli + ($second * 1000) + ($min * 60 * 1000) + ($hour * 60 * 60 * 1000);
    }

    public function replaceText($phrase, $replace_rules)
    {
        $text = $phrase;
        foreach ($replace_rules as $rule) {
            if ($rule->similar) {
                if(preg_match("/$rule->find_phrase(\s|$|\.|\,)/", $text)) {
                    $text = str_replace($rule->find_phrase, $rule->replace_phrase, $text);
                }
            } else {

                if (strpos($text , $rule->find_phrase) !== false) {
                    if ($rule->last_character) {
                        if (str_ends_with($text, $rule->find_phrase)) {
                            $separated = explode(' ' , $text);
                            $last_word = end($separated);
                            array_pop($separated);
                            $separated[] = str_replace($rule->find_phrase, $rule->replace_phrase, $last_word);
                            $text = implode(' ', $separated);
                        }
                    } else {
                        $text = str_replace($rule->find_phrase, $rule->replace_phrase, $text);
                    }
                }
            }
        }

        return $text;
    }
}

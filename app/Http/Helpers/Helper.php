<?php

namespace App\Http\Helpers;

class Helper
{
    public function readableNumber(int $num) :string
    {
        switch (true) {
            case ($num >= 1000 && $num <= 9999):
                return number_format($num / 1000 , 1) . 'k';
            case ($num >= 10000 && $num <= 999999):
                return number_format($num / 1000 , 0) . 'k';
            case ($num >= 1000000 && $num <= 100000000):
                return number_format($num / 1000000 , 1) . 'm';
            case ($num < 1000):
            default:
                return strval($num);
        }
    }
}

<?php

namespace App\Http\Helpers;

use App\Models\UserWord;

class UserWordHelper extends Helper
{
    function getUserWord($user_id , $word)
    {
        return UserWord::where('word',$word)->where('user_id',$user_id)->first();
    }

    public function getUserWordComment($user_id , $word)
    {
        $user_word = UserWord::where('word',$word)->where('user_id',$user_id)->first();
        if($user_word){
            return $user_word->comment_user;
        } else {
            return null;
        }
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Exception;
use App\Models\User;
use Illuminate\Support\Str;

class TestController extends Controller
{
    public function generateCodeIntroduce()
    {
        foreach (User::all() as $user)
        {
            do{
                $code_introduce = Str::random(6);
                $code_introduce_exists = User::where('code_introduce' , $code_introduce)->exists();
            }while($code_introduce_exists);

            $user->code_introduce = $code_introduce;
            $user->update();
        }
        return 'done :)';
    }
}

<?php

namespace App\Http\Controllers\admin;

use App\Models\Admin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Laravel\Sanctum\PersonalAccessToken;


class AdminController extends Controller
{
    public function login(Request $request){
        $messages = array(
            'username.required' => 'نام کاربری نمی تواند خالی باشد',
            'password.required' => 'پسورد نمی تواند خالی باشد',
        );

        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "لاگین با شکست مواجه شد",
            ], 400);
        }

        $admin = $this->getAdmin($request->username, $request->password);
        if($admin){
            $admin->tokens()->delete();
            $token = $admin->createToken(config('app.name').'-admin');
            $admin->api_token = $token->plainTextToken;
            return response()->json([
                'data' => $admin,
                'errors' => null,
                'message' => "لاگین موفقیت آمیز بود",
            ]);
        }else{
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "چنین کاربری وجود ندارد",
            ], 400);
        }
    }

    public function getAdmin($username, $password){
        return Admin::where('username', $username)->where('password', $password)->first();
    }

    public function get_admin_data(Request $request)
    {
        $token = $request->header('ApiToken');
        $admin = $this->getAdminByToken($token);
        if(!$admin){
            return response()->json([
                'data' => null,
                'errors' => null,
                'message' => "خطا در دریافت اطلاعات ادمین ...",
            ], 404);
        }

        return response()->json([
            'data' => $admin,
            'errors' => null,
            'message' => "اطلاعات ادمین با موفقیت دریافت شد",
        ]);
    }

    public static function getAdminByToken($api_token)
    {
        $token = PersonalAccessToken::findToken($api_token);
        return $token->tokenable;
    }
}

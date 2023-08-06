<?php

namespace App\Http\Controllers\admin;

use App\Models\Admin;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;


class AdminController extends Controller
{

    public function login(Request $request){
        $messsages = array(
            'username.required' => 'نام کاربری نمی تواند خالی باشد',
            'password.required' => 'پسورد نمی تواند خالی باشد',
        );

        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "لاگین با شکست مواجه شد",
            ];
            return response()->json($arr, 400);
        }

        $admin = $this->getAdmin($request->username, $request->password);
        if($admin){
            $admin->api_token = Str::random(64);
            $admin->save();
            $arr = [
                'data' => $admin,
                'errors' => null,
                'message' => "لاگین موفقیت آمیز بود",
            ];
            return response()->json($arr, 200);

        }else{
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "چنین کاربری وجود ندارد",
            ];
            return response()->json($arr, 400);
        }
    }


    public function getAdmin($username, $password){
        return Admin::where('username', $username)->where('password', $password)->first();
    }

    public function get_admin_data(Request $request)
    {
        $token = $request->header('ApiToken');
        $admin =  $admin = $this->getAdminByToken($token);
        if(!$admin){
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "خطا در دریافت اطلاعات ادمین ...",
            ];
            return response()->json($arr, 404);
        }
        $arr = [
            'data' => $admin,
            'errors' => null,
            'message' => "اطلاعات ادمین با موفقیت دریافت شد",
        ];
        return response()->json($arr, 200);
    }

    public static function getAdminByToken($api_token)
    {
        $admin =  Admin::where('api_token', $api_token)->first();
        return $admin;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public static function isUserSubscriptionValid(Request $request)
    {
        $api_token = $request->header("ApiToken");
//        $data = User::header('ApiToken', $api_token)->first();

        $data = self::getUserByToken($api_token);

        $expire_at = $data->expired_at;
        $now = Carbon::now();

        if ($now <= $expire_at)
            return 1;
        else
            return 0;

    }
    
    
    
    public function saveFcmRefreshTokenInServer(Request $request)
    {

        $messsages = array(
            'token.required' => 'توکن الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ], $messsages);


        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش کاربر شکست خورد"
            ];
            return response()->json($arr, 400);
        }



        $api_token = $request->header("ApiToken");
        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user->fcm_refresh_token = $request->token;
                        unset($user['days_remain']);
            unset($user['hours_remain']);
            unset($user['minutes_remain']);
            $user->save();

            $response = [
                'data' => null,
                'errors' => [
                ],
                'message' => " ویرایش کاربر با موفقیت انجام شد",
            ];
            return response()->json($response, 200);
        } else {

            $response = [
                'data' => null,
                'errors' => [
                ],
                'message' => " مشکل در احراز هویت کاربر",
            ];
            return response()->json($response, 401);
        }
    }

    
    public function addRewardsByUser(Request $request)
    {
        $api_token = $request->header("ApiToken");
        $user = self::getUserByToken($api_token);
        if ($user) {
            $expired = Carbon::parse($user->expired_at);
            if( $expired> Carbon::now()){
                $user->expired_at = $expired->addMinutes(30);
            }else{
                $user->expired_at =Carbon::now()->addMinutes(30);
            }

            unset($user['days_remain']);
            unset($user['hours_remain']);
            unset($user['minutes_remain']);
            $user->save();
            $response = [
                'data' => null,
                'errors' => [
                ],
                'message' => "اشتراک شما با موفقیت تمدید شد",
            ];
            return response()->json($response, 200);
    
        } else {

            $response = [
                'data' => null,
                'errors' => [
                ],
                'message' => "مشکل در احراز هویت",
            ];
            return response()->json($response, 401);
        }
    }


    public static function getUserByToken($api_token)
    {
        $user =  User::where('api_token', $api_token)->first();

        $now = Carbon::now();
        $expiredAt = $user->expired_at;
        $diffInDay = $now->diffInDays($expiredAt);
        $diffInHours = $now->diffInHours($expiredAt);
        $diffInMinutes = $now->diffInMinutes($expiredAt);

        if($now > $expiredAt){
            $user->days_remain = 0;
            $user->hours_remain = 0;
            $user->minutes_remain = 0;
        }else{
            $user->days_remain = $diffInDay;
            $user->hours_remain = $diffInHours;
            $user->minutes_remain = $diffInMinutes;
        }
        return $user;
    }



    public static function getUserIdByPhoneNumber($phone_number)
    {
        $phone_number = str_replace("+98", "", $phone_number);
        if (substr($phone_number, 0, 1) == "0") {
            $phone_number = substr($phone_number, -strlen($phone_number) + 1);
        }
        $user = User::where('phone_number', $phone_number)->first();
        if ($user) {
            return $user->id;
        } else {
            return null;
        }

    }

    public static function changeTokenAndReturnUser($phone_number)
    {
        $phone_number = str_replace("+98", "", $phone_number);
        if (substr($phone_number, 0, 1) == "0") {
            $phone_number = substr($phone_number, -strlen($phone_number) + 1);
        }
        $user = User::where('phone_number', $phone_number)->first();
        $user->api_token = Str::random(64);
        $user->save();
        return $user;
    }

    public static function addUser($phone_number)
    {
        $phone_number = str_replace("+98", "", $phone_number);
        if (substr($phone_number, 0, 1) == "0") {
            $phone_number = substr($phone_number, -strlen($phone_number) + 1);
        }
        $user = new User();
        $user->phone_number = $phone_number;
        $user->expired_at = Carbon::now()->addDays(5); //Free subscription
        $user->api_token = Str::random(64);

        $user->save();
        return $user;
    }

    public static function setUserNewSubscription(Request $request)
    {
        $api_token = $request->header("ApiToken");
        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $plan = SubscriptionController::getSubscriptionById($request->id_plan);
            $daysSubscription = $plan->subscription_days;
            $expired = Carbon::parse($user->expired_at);
            if( $expired> Carbon::now()){
                $user->expired_at = $expired->addDays($daysSubscription);
            }else{
                $user->expired_at =Carbon::now()->addDays($daysSubscription);
            }

            unset($user['days_remain']);
            unset($user['hours_remain']);
            unset($user['minutes_remain']);
            $user->save();
        } else {

            $response = [
                'data' => null,
                'errors' => [
                ],
                'message' => " مشکل در احراز هویت، درصورتی که از حساب شما کم شد و عملیاتی انجام نشد با پشتیبان تماس بگیرید",
            ];
            return response()->json($response, 401);
        }
    }

    public function getUser(Request $request)
    {

        $api_token = $request->header("ApiToken");
//        $data = User::header('ApiToken', $api_token)->first();

        $data = $this->getUserByToken($api_token);

        $id = $data->id;
        if ($id != null) {

            if ($data != null) {
                $response = array(
                    "data" => $data,
                    "message" => "گرفتن اطلاعات با موفقیت انجام شد",
                    "error" => null);

                return response()->json($response, 200);
            } else {
                $response = array(
                    "data" => $data,
                    "message" => "داده ای پیدا نشد.",
                    "error" => null);

                return response()->json($response, 400);
            }
        } else {

            $errors["id"] = array("لطفا شناسه را وارد کنید");
            $response = array(
                "data" => null,
                "message" => "خطا در گرفتن اطلاعات",
                "error" => $errors);

            return response()->json($response, 400);
        }
    }
    
        public static function getUserById($id){
        return  User::where('id', $id)->first();
    }


}

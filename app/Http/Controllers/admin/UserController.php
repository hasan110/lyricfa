<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\SubscriptionController;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Morilog\Jalali\Jalalian;

class UserController extends Controller
{
    public static function isUserSubscriptionValid(Request $request)
    {
        $api_token = $request->header("ApiToken");

        $data = self::getUserByToken($api_token);

        $expire_at = $data->expired_at;
        $now = Carbon::now();

        if ($now <= $expire_at)
            return 1;
        else
            return 0;

    }

    public static function getUserByToken($api_token)
    {
        $user = User::where('api_token', $api_token)->first();

        $now = Carbon::now();
        $expiredAt = $user->expired_at;
        $diff = $now->diff($expiredAt);

        if ($now > $expiredAt) {
            $user->days_remain = 0;
        } else {
            $user->days_remain = $diff->days;
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
        $user->expired_at = Carbon::now()->addDays(7); //Free subscription
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
            if ($expired > Carbon::now()) {
                $user->expired_at = $expired->addDays($daysSubscription);
            } else {
                $user->expired_at = Carbon::now()->addDays($daysSubscription);
            }

            unset($user['days_remain']);
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
        $data = self::getUserByToken($api_token);
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

    public static function getUserById($id)
    {
        return User::where('id', $id)->first();
    }

    public function usersList(Request $request)
    {
        $users = User::query();

        if($request->search_key){
            $users = $users->where('phone_number', 'LIKE', "%{$request->search_key}%");
        }
        if($request->sort_by){
            switch ($request->sort_by){
                case 'newest':
                    $users = $users->orderBy('id' , 'desc');
                break;
                case 'oldest':
                    $users = $users->orderBy('id' , 'asc');
                break;
                case 'most_subscribed':
                    $users = $users->orderBy('expired_at' , 'desc');
                break;
            }
        }

        $users = $users->paginate(50);

        foreach ($users as $user)
        {
            $expire = '<span class="red--text">منقضی شده</span>';

            if ($user->expired_at)
            {
                $expired_at = Carbon::parse($user->expired_at);
                $diff = Carbon::now()->diffInDays($expired_at , false);
                if($diff > 0)
                {
                    $expire = '<b>'. $diff .' روز </b>';
                }
            }

            $user['expire'] = $expire;
            $user->persian_created_at = Jalalian::forge($user->created_at)->format('%Y-%m-%d H:i');
        }

        $response = [
            'data' => $users,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);
    }


    public function singleUser(Request $request)
    {
        $messsages = array(
            'id.required' => 'شناسه کاربر الزامی است',
            'id.numeric' => 'شناسه کاربر باید عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " گرفتن اطلاعات کاربر شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $user = User::where('id',  $request->id)->first();

        $subscriptions =  Report::where('user_id', $request->id)->orderBy('id', 'DESC')->take(100)->get();
        foreach ($subscriptions as &$subscription) {
            $subscription['persian_created_at'] = Jalalian::forge($subscription->created_at)->format('%Y-%m-%d H:i');
        }
        $user->subscription = $subscriptions;

        $now = Carbon::now();
        $expiredAt = $user->expired_at;

        if ($now > $expiredAt) {
            $user->days_remain = 0;
        } else {
            $diff = $now->diff($expiredAt);
            $user->days_remain = $diff->days;
        }

        $response = [
            'data' => $user,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);
    }

    public function saveFcmRefreshTokenInServer(Request $request)
    {
        $messages = array(
            'token.required' => 'توکن الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'token' => 'required'
        ], $messages);


        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "ویرایش کاربر شکست خورد"
            ];
            return response()->json($arr, 400);
        }


        $api_token = $request->header("ApiToken");
        $user = \App\Http\Controllers\UserController::getUserByToken($api_token);
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

    public static function getListUsersTokenNotifications(){
        $users = User::all();

        foreach ($users as $user) {
            if($user->fcm_refresh_token)
            {
                $tokens[] = $user->fcm_refresh_token;
            }
        }
        return $tokens;
    }
}

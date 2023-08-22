<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Notification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PharIo\Manifest\ElementCollectionException;

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

    public static function addUser($phone_number , $referral_code = null)
    {
        $phone_number = str_replace("+98", "", $phone_number);
        if (substr($phone_number, 0, 1) == "0") {
            $phone_number = substr($phone_number, -strlen($phone_number) + 1);
        }

        do{
            $code_introduce = Str::random(6);
            $code_introduce_exists = User::where('code_introduce' , $code_introduce)->exists();
        }while($code_introduce_exists);

        $user = new User();
        $user->phone_number = $phone_number;
        $user->expired_at = Carbon::now()->addDays(2); //Free subscription
        $user->api_token = Str::random(64);
        $user->code_introduce = $code_introduce;
        $user->referral_code = $referral_code;

        $user->save();

        if ($referral_code)
        {
            try {
                self::referOperations('register' , $user , 2);
            }catch (Exception $e){}
        }

        return $user;
    }

    private static function referOperations(string $type , $user , $addDays)
    {
        if(!$user->referral_code) return false;
        $referral_code_user = self::getReferralCodeUser($user->referral_code);
        if(!$referral_code_user) return false;

        switch ($type){
            case 'register':

                $number = substr($user->phone_number , 0 , 3) . '***' . substr($user->phone_number , -4);
                $message = 'شما کاربر '.$number.' را به اپلیکیشن لیریکفا معرفی کردید. به ازای آن '. $addDays .' روز اشتراک رایگان دریافت کردید که هم اکنون میتوانید استفاده کنید.';
                $notification_data = [
                    'title' => 'معرفی کاربر',
                    'body' => $message,
                    'token' => $referral_code_user->fcm_refresh_token,
                ];
                Notification::send('google_notification' , $notification_data);

            break;
            case 'subscription':

                $number = substr($user->phone_number , 0 , 3) . '***' . substr($user->phone_number , -4);
                $message = 'شما از معرفی کاربر '.$number.' و خرید اشتراک توسط این کاربر '.$addDays.' روز اشتراک رایگان دریافت کردید که هم اکنون میتوانید استفاده کنید.';
                $notification_data = [
                    'title' => 'اشتراک رایگان',
                    'body' => $message,
                    'token' => $referral_code_user->fcm_refresh_token,
                ];
                Notification::send('google_notification' , $notification_data);

            break;
            default:
                return false;
        }

        if($referral_code_user->expired_at > Carbon::now())
        {
            $new_expiry = Carbon::parse($referral_code_user->expired_at)->addDays($addDays);
        }else{
            $new_expiry = Carbon::now()->addDays($addDays);
        }

        $referral_code_user->expired_at = $new_expiry;
        $referral_code_user->save();

        return true;
    }

    public static function checkReferralCode($referral_code)
    {
        if (!$referral_code) return null;

        if(User::where('code_introduce' , $referral_code)->exists())
        {
            return $referral_code;
        }

        return null;
    }

    public static function getReferralCodeUser($referral_code)
    {
        if (!$referral_code) return null;

        if($user = User::where('code_introduce' , $referral_code)->first())
        {
            return $user;
        }

        return null;
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

            if ($user->referral_code)
            {
                try {
                    $addDays = floor($daysSubscription * 0.1);
                    self::referOperations('subscription' , $user , $addDays);
                }catch (Exception $e){}
            }

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

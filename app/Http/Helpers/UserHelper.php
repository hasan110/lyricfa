<?php

namespace App\Http\Helpers;

use App\Models\Subscription;
use App\Models\User;
use App\Services\Notification;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use Laravel\Sanctum\PersonalAccessToken;

class UserHelper extends Helper
{
    public function getUserByToken($api_token)
    {
        $token = PersonalAccessToken::findToken($api_token);
        if (!$token) {
            return null;
        }
        return $token->tokenable;
    }

    public function getUserById($user_id)
    {
        return User::where('id', $user_id)->first();
    }

    public function getUserByPhoneNumber($phone_number)
    {
        $phone_number = str_replace("+98", "", $phone_number);
        if (substr($phone_number, 0, 1) == "0") {
            $phone_number = substr($phone_number, -strlen($phone_number) + 1);
        }
        return User::where('phone_number', $phone_number)->first();
    }

    public function getUserByAbsolutePhoneNumber($phone_number , $prefix)
    {
        return User::where('phone_number', $phone_number)->where('prefix_code', $prefix)->first();
    }

    public function getUserByEmail($email)
    {
        return User::where('email', $email)->first();
    }

    public function generateTokenByPhoneNumber($phone_number , $corridor = 'app')
    {
        $phone_number = str_replace("+98", "", $phone_number);
        if (substr($phone_number, 0, 1) == "0") {
            $phone_number = substr($phone_number, -strlen($phone_number) + 1);
        }
        $user = User::where('phone_number', $phone_number)->first();
        $tokens = $user->tokens()->latest()->get();
        foreach ($tokens as $key => $token) {
            if ($key === 0) continue;
            $token->delete();
        }
        $token = $user->createToken(config('app.name') . '-' . $corridor);
        $user->api_token = $token->plainTextToken;
        return $user;
    }

    public function generateTokenByAbsolutePhoneNumber($phone_number , $prefix , $corridor = 'app')
    {
        $user = User::where('phone_number', $phone_number)->where('prefix_code', $prefix)->first();
        $tokens = $user->tokens()->latest()->get();
        foreach ($tokens as $key => $token) {
            if ($key === 0) continue;
            $token->delete();
        }
        $token = $user->createToken(config('app.name') . '-' . $corridor);
        $user->api_token = $token->plainTextToken;
        return $user;
    }

    public function generateTokenByEmail($email , $corridor = 'app')
    {
        $user = User::where('email', $email)->first();
        $tokens = $user->tokens()->latest()->get();
        foreach ($tokens as $key => $token) {
            if ($key === 0) continue;
            $token->delete();
        }
        $token = $user->createToken(config('app.name') . '-' . $corridor);
        $user->api_token = $token->plainTextToken;
        return $user;
    }

    public function addUser($phone_number , $referral_code = null , $prefix = '98', $corridor = 'app')
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
        $user->prefix_code = $prefix;
        $user->expired_at = Carbon::now()->addDays(2);
        $user->code_introduce = $code_introduce;
        $user->referral_code = $referral_code;
        $user->corridor = $corridor;

        $user->save();

        $token = $user->createToken(config('app.name').'-'.$corridor);
        $user->api_token = $token->plainTextToken;

        if ($referral_code)
        {
            try {
                $this->referOperations('register' , $user , 2);
            } catch (Exception $e) {}
        }

        return $user;
    }

    public function addUserByEmail($email , $referral_code = null , $corridor = 'app')
    {
        do{
            $code_introduce = Str::random(6);
            $code_introduce_exists = User::where('code_introduce' , $code_introduce)->exists();
        }while($code_introduce_exists);

        $user = new User();
        $user->email = $email;
        $user->expired_at = Carbon::now();
        $user->code_introduce = $code_introduce;
        $user->referral_code = $referral_code;
        $user->corridor = $corridor;

        $user->save();

        $token = $user->createToken(config('app.name').'-'.$corridor);
        $user->api_token = $token->plainTextToken;

        if ($referral_code)
        {
            try {
                $this->referOperations('register' , $user , 2);
            } catch (Exception $e) {}
        }

        return $user;
    }

    public function getUserDetailByToken($api_token)
    {
        $token = PersonalAccessToken::findToken($api_token);
        if (!$token) {
            return null;
        }
        $user = $token->tokenable;
        $user->api_token = $api_token;

        $now = Carbon::now();
        $expiredAt = $user->expired_at;
        $diffInDay = $now->diffInDays($expiredAt);
        $diffInHours = $now->diffInHours($expiredAt);
        $diffInMinutes = $now->diffInMinutes($expiredAt);

        if ($now > $expiredAt) {
            $user->days_remain = 0;
            $user->hours_remain = 0;
            $user->minutes_remain = 0;
        } else {
            $user->days_remain = $diffInDay;
            $user->hours_remain = $diffInHours;
            $user->minutes_remain = $diffInMinutes;
        }

        if (Carbon::now()->diffInMinutes($user->created_at) <= 10) {
            $user->new_user = true;
        } else {
            $user->new_user = false;
        }

        return $user;
    }

    public function isUserSubscriptionValid($api_token)
    {
        $user = $this->getUserByToken($api_token);
        if (!$user) return 0;

        $expire_at = $user->expired_at;
        $now = Carbon::now();

        if ($now <= $expire_at)
            return 1;
        else
            return 0;
    }

    public function getSubscriptionById($id_plan)
    {
        return Subscription::where('id', $id_plan)->first();
    }

    public function setUserSubscription($user , $plan_id)
    {
        if ($user) {
            $plan = $this->getSubscriptionById($plan_id);
            if (!$plan) return false;

            $expired = Carbon::parse($user->expired_at);
            if($expired > Carbon::now()){
                $expired_at = $expired->addDays($plan->subscription_days);
            }else{
                $expired_at =Carbon::now()->addDays($plan->subscription_days);
            }

            $user->update([
                'expired_at' => $expired_at
            ]);

            if ($user->referral_code)
            {
                try {
                    $addDays = floor($plan->subscription_days * 0.1);
                    $this->referOperations('subscription' , $user , $addDays);
                } catch (Exception $e){}
            }

            return true;
        } else {
            return false;
        }
    }

    public function referOperations(string $type , $user , $addDays)
    {
        if(!$user->referral_code) return false;
        $referral_code_user = $this->getReferralCodeUser($user->referral_code);
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

    public function checkReferralCode($referral_code)
    {
        if (!$referral_code) return null;
        if(User::where('code_introduce' , $referral_code)->exists())
        {
            return $referral_code;
        }
        return null;
    }

    public function getReferralCodeUser($referral_code)
    {
        if (!$referral_code) return null;
        if($user = User::where('code_introduce' , $referral_code)->first())
        {
            return $user;
        }
        return null;
    }
}

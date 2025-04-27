<?php

namespace App\Http\Helpers;

use App\Models\Category;
use App\Models\Film;
use App\Models\Music;
use App\Models\Report;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\User;
use App\Models\UserWord;
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
        $user->expired_at = Carbon::now();
        $user->last_subscription_warning = Carbon::now();
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
        $user->last_subscription_warning = Carbon::now();
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

        $timing = Setting::getItem('subscription_warning_timing');
        if (Carbon::parse($user->last_subscription_warning)->addHours($timing) < Carbon::now() && $user->minutes_remain === 0) {
            $user->show_warning = true;
            User::whereKey($user->getKey())->update(['last_subscription_warning' => Carbon::now()]);
        } else {
            $user->show_warning = false;
        }

        $user->latest_views = $this->getLatestViews($user);
        $user->word_review_count = $this->getLightenerBox($user->id , true);
        $user->notifications = [];

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

    public function getSubscriptions($user_id, $all = false)
    {
        $subscriptions = Report::where('user_id', $user_id);
        if (!$all) {
            $subscriptions = $subscriptions->where('type', 1);
        }
        return $subscriptions->orderBy('id', 'DESC')->take(100)->get();
    }

    public function getLightenerBox($user_id, $all_reviews_count = false)
    {
        $box_data = [];
        $all_reviews = 0;
        for($i = 0 ; $i <= 5 ; $i++)
        {
            $count = UserWord::where('user_id', $user_id)->where('status', $i)->count();
            $words_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('type', 0)->count();
            $idioms_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('type', 1)->count();
            $comment_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('type', 2)->count();
            $grammars_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('type', 3)->count();
            $reviews_count = 0;
            $date = Carbon::now();
            switch ($i) {
                case 0:
                    $reviews_count = $count;
                    break;
                case 1:
                    $reviews_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('updated_at', '<=' , $date->subDays(1))->count();
                    break;
                case 2:
                    $reviews_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('updated_at', '<=' , $date->subDays(2))->count();
                    break;
                case 3:
                    $reviews_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('updated_at', '<=' , $date->subDays(4))->count();
                    break;
                case 4:
                    $reviews_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('updated_at', '<=' , $date->subDays(8))->count();
                    break;
                case 5:
                    $reviews_count = UserWord::where('user_id', $user_id)->where('status', $i)->where('updated_at', '<=' , $date->subDays(16))->count();
                    break;
            }
            if ($i !== 5) {
                $all_reviews += $reviews_count;
            }
            $data = [
                'status' => $i,
                'total_count' => $count,
                'words_count' => $words_count,
                'idioms_count' => $idioms_count,
                'comments_count' => $comment_count,
                'grammars_count' => $grammars_count,
                'reviews_count' => $reviews_count,
            ];
            $box_data[$i] = $data;
        }

        if ($all_reviews_count) {
            return $all_reviews;
        }

        return $box_data;
    }

    public function getLatestViews($user)
    {
        $views = $user->views()->where('percentage' , '<' , 100)->with('viewable')->orderBy('updated_at', 'desc')->take(20)->get();
        $final_list = [];

        foreach ($views as $view) {
            if ($view->viewable_type === Film::class) {
                $film = $view->viewable;
                $final_list[] = [
                    'id' => $film->id,
                    'type' => Film::class,
                    'persian_title' => $film->persian_name,
                    'english_title' => $film->english_name,
                    'poster' => $film->film_poster,
                    'percentage' => $view->percentage,
                ];
            }
            if ($view->viewable_type === Music::class) {
                $music = $view->viewable;
                $final_list[] = [
                    'id' => $music->id,
                    'type' => Music::class,
                    'persian_title' => $music->persian_name,
                    'english_title' => $music->name,
                    'poster' => $music->music_poster,
                    'percentage' => $view->percentage,
                ];
            }
            if ($view->viewable_type === Category::class) {
                $category = $view->viewable;
                $final_list[] = [
                    'id' => $category->id,
                    'type' => Category::class,
                    'persian_title' => $category->subtitle,
                    'english_title' => $category->title,
                    'poster' => $category->category_poster,
                    'percentage' => $view->percentage,
                ];
            }
        }

        return $final_list;
    }
}

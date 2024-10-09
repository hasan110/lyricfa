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
    public function usersList(Request $request)
    {
        $users = User::query();

        if($request->search_key){
            $users = $users->where('phone_number', 'LIKE', "%{$request->search_key}%")
                ->orWhere('id', '=', $request->search_key);
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
            $user->has_subscription = false;

            if ($user->expired_at)
            {
                $expired_at = Carbon::parse($user->expired_at);
                $diffInMinute = Carbon::now()->diffInMinutes($expired_at, false);
                if($diffInMinute > 0)
                {
                    $diff = Carbon::now()->diff($expired_at);
                    $years_days = $diff->y > 0 ? $diff->y * 365 : 0;
                    $months_days = $diff->m > 0 ? $diff->m * 30 : 0;
                    $days = $years_days + $months_days + $diff->d;
                    if ($days > 3) {
                        $user->has_subscription = true;
                    }
                    if ($days > 0) {
                        if ($diff->h > 0) {
                            $expire = $days .' روز و ' . $diff->h . ' ساعت';
                        } else {
                            $expire = $days .' روز و '.$diff->i .' دقیقه';
                        }
                    } else {
                        if ($diff->h > 0) {
                            $expire = $diff->h .' ساعت و '.$diff->i .' دقیقه';
                        } else {
                            $expire = $diff->i .' دقیقه';
                        }
                    }
                }
            }

            $user['expire'] = $expire;
            $user->persian_created_at = Jalalian::forge($user->created_at)->format('%Y-%m-%d H:i');
        }

        return response()->json([
            'data' => $users,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }


    public function singleUser(Request $request)
    {
        $messages = array(
            'id.required' => 'شناسه کاربر الزامی است',
            'id.numeric' => 'شناسه کاربر باید عدد باشد'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " گرفتن اطلاعات کاربر شکست خورد",
            ], 400);
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

        return response()->json([
            'data' => $user,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
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

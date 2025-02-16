<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\UserHelper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

class UserController extends Controller
{
    public function usersList(Request $request)
    {
        $users = User::query();

        if($request->search_key){
            $users = $users->where('phone_number', 'LIKE', "%{$request->search_key}%")
                ->orWhere('email', 'LIKE', "%{$request->search_key}%")
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
            $expire = '<span class="orange--text darken-4">بدون اشتراک</span>';
            $user->has_subscription = false;

            if ($user->expired_at)
            {
                $expired_at = Carbon::parse($user->expired_at);
                $diffInMinute = Carbon::now()->diffInMinutes($expired_at, false);
                if($diffInMinute > 0)
                {
                    $user->has_subscription = true;
                    $diff = Carbon::now()->diff($expired_at);
                    $days = Carbon::now()->diffInDays($expired_at);
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

        $user = User::where('id',  $request->id)->with(['comments', 'likes', 'music_orders', 'playlists' => function ($q) {
            $q->with('musics');
        }])->first();
        if (!$user) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "شناسه معتبر نیست",
            ], 400);
        }

        $user->subscription = (new UserHelper())->getSubscriptions($user->id, true);

        $tokens = $user->tokens()->latest()->get();
        foreach ($tokens as &$token) {
            $token->persian_last_used_at = Jalalian::forge($token->last_used_at)->addMinutes(3.5*60)->format('%Y-%m-%d H:i') . ' - ' . Jalalian::forge($token->last_used_at)->ago();
            $token->persian_created_at = Jalalian::forge($token->created_at)->addMinutes(3.5*60)->format('%Y-%m-%d H:i') . ' - ' . Jalalian::forge($token->created_at)->ago();
        }
        $user->access_tokens = $tokens;
        $user->lightener_box_data = (new UserHelper())->getLightenerBox($user->id);

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

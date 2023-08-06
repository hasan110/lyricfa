<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Morilog\Jalali\Jalalian;

class CheckApiAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $api_token = $request->header("ApiToken");
        // dd($request->header("ApiToken"));
        // return response()->json($api_token, 401);
        if (!$api_token) {
            // return Response::error(null , 'کاربر احراز هویت نشده' , null , 401);

            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "اوه خطا در احراز هویت",
            ];
            return response()->json($arr, 401);

        }

        $user = User::where('api_token', $api_token)->first();
        if (!$user) {
            // return Response::error(null , 'کاربر احراز هویت نشده' , null , 401);

            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "خطا در احراز هویت",
            ];
            return response()->json($arr, 401);

        }

           
        // return response()->json(Jalalian::fromCarbon($user->updated_at)->format('Y-m-d H:i:s'), 200);

         return $next($request);
    }
}
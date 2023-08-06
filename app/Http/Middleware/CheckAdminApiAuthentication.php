<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Http\Request;

class CheckAdminApiAuthentication
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
        if (!$api_token) {

            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "اوه خطا در احراز هویت",
            ];
            return response()->json($arr, 401);

        }

        $admin = Admin::where('api_token', $api_token)->first();
        if (!$admin) {

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

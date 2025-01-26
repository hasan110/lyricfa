<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class CheckApiAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
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

        $token = PersonalAccessToken::findToken($api_token);
        if (!$token) {
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "اوه خطا در احراز هویت",
            ];
            return response()->json($arr, 401);
        }
        $user = $token->tokenable;

        if (!$user) {
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "خطا در احراز هویت",
            ];
            return response()->json($arr, 401);

        }
        $token->last_used_at = Carbon::now();
        $token->save();

        return $next($request);
    }
}

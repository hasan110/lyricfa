<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Response;

class ResponseServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }
    
    public function boot()
    {
        Response::macro('success', function($data, $message , $http_status_code = 200) {
            return response()->json([
                'data'=> $data,
                'message'=> $message,
                'errors' => null,
            ] , $http_status_code);
        });

        Response::macro('error', function($data, $message, $errors , $http_status_code = 400) {
            return response()->json([
                'data'=> $data,
                'message'=> $message,
                'errors' => $errors,
            ] , $http_status_code);
        });
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class MerchentZarinPalController extends Controller
{
    public function getMerchentId(Request $request){
        $merchant = [];
        foreach (Setting::where('key', 'like', '%zarinpal%')->get() as $setting) {
            $merchant[$setting->title] = $setting->value;
        }

        if(!empty($merchant)){
            $response = [
                'data' => $merchant,
                'errors' => [],
                'message' => "مرچنت با موفقیت گرفته شد"
            ];
            return response()->json($response);
        }else{
            $response = [
                'data' => null,
                'errors' => [
                ],
                'message' => "مرچنت آیدی وجود ندارد"
            ];
            return response()->json($response, 400);

        }
    }

}

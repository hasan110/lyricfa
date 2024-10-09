<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class MerchentZarinPalController extends Controller
{
    public function getMerchentId()
    {
        $merchant = [];
        foreach (Setting::where('key', 'like', '%zarinpal%')->get() as $setting) {
            $merchant[$setting->title] = $setting->value;
        }

        if (!empty($merchant)) {
            return response()->json([
                'data' => $merchant,
                'errors' => [],
                'message' => "مرچنت با موفقیت گرفته شد"
            ]);
        } else {
            return response()->json([
                'data' => null,
                'errors' => [
                ],
                'message' => "مرچنت آیدی وجود ندارد"
            ], 400);
        }
    }

}

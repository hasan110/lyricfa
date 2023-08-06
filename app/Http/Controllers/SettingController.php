<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Hamcrest\Core\Set;
use Illuminate\Http\Request;


class SettingController extends Controller
{

    public function getSetting()
    {

        $getSetting = Setting::first();

        $response = [
            'data' => $getSetting,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد"
        ];
        return response()->json($response, 200);
    }


}

<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class SettingController extends Controller
{

    public function getSetting(Request $request)
    {
        $settings = Setting::all();
        $result = [];

        foreach ($settings as $setting) {
            if (is_numeric($setting->value)) {
                $setting->value = intval($setting->value);
            }
            $result[$setting->key] = $setting->value;
        }

        $response = [
            'data' => $result,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد"
        ];
        return response()->json($response);
    }

    public function editSetting(Request $request)
    {
        $messages = array(
            'app_version_code.required' => 'کد ورژن را وارد کنید',
            'app_version_name.required' => 'نام ورژن را وارد کنید',
        );

        $validator = Validator::make($request->all(), [
            'app_version_code' => 'required',
            'app_version_name' => 'required',
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " ویرایش تنظیمات آهنگ شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $setting = Setting::whereIn('key', ['app_version_code','app_version_name','maintenance_mode'])->get();
        foreach ($setting as $setting) {
            if ($request->has($setting->key)) {
                $setting->update([
                    'value' => $request->input($setting->key),
                ]);
            }
        }

        $arr = [
            'data' => null,
            'errors' => null,
            'message' => "تنظیمات با موفقیت به روز شد.",
        ];

        return response()->json($arr);
    }
}

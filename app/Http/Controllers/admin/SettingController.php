<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function getSetting()
    {
        return response()->json([
            'data' => Setting::fetch(true , true),
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد"
        ]);
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
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " ویرایش تنظیمات آهنگ شکست خورد",
            ], 400);
        }

        $settings = Setting::all();
        foreach ($settings as $setting) {
            if ($request->has($setting->key)) {
                $setting->update([
                    'value' => $request->input($setting->key),
                ]);
            }
        }

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => "تنظیمات با موفقیت به روز شد.",
        ]);
    }
}

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

        $getSetting = Setting::first();

        $response = [
            'data' => $getSetting,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد"
        ];
        return response()->json($response, 200);
    }

    public function getSettingById()
    {

       return Setting::first();

    }


    public function editSetting(Request $request)
    {


        $messsages = array(
            'app_version_code.required' => 'کد ورژن را وارد کنید',
            'app_version_name.required' => 'نام ورژن را وارد کنید',
        );

        $validator = Validator::make($request->all(), [
            'app_version_code' => 'required',
            'app_version_name' => 'required',
        ], $messsages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " ویرایش تنظیمات آهنگ شکست خورد",
            ];
            return response()->json($arr, 400);
        }


        $setting = $this->getSettingById();
        if (isset($setting)) {
            $setting->app_version_code = $request->app_version_code;
            $setting->app_version_name = $request->app_version_name;
            $setting->maintenance_mode = $request->maintenance_mode;
            $setting->save();


            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "تنظیمات با موفقیت به روز شد.",
            ];

            return response()->json($arr, 200);
        } else {
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "خطا در به روز رسانی.",
            ];

            return response()->json($arr, 400);
        }

    }


}

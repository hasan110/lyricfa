<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Models\SmsVerify;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SmsVerifyController extends Controller
{
    public function checkActivateCode(Request $request)
    {
        $messages = array(
            'phone_number.required' => 'شماره تماس الزامی است',
            'code.required' => 'کد ارسالی الزامی است',
        );

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required',
            'code' => 'required'
        ], $messages);


        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "بررسی کد فعال سازی شکست خورد"
            ];
            return response()->json($arr, 400);
        }

        if ($request->corridor === 'web-app') {
            $corridor = 'web-app';
        } else {
            $corridor = 'app';
        }

        if( $request->phone_number == 1234567890 && $request->code == 1234 && $request->type == "login"){ // account google play
            $user = (new UserHelper())->generateTokenByPhoneNumber($request->phone_number , $corridor);
            return response()->json([
                'data' => $user,
                'errors' => [],
                'message' => "اطلاعات کاربر با موفقیت دریافت شد"
            ]);
        }

        $phone_number = str_replace("+98", "", $request->phone_number);
        if (substr($phone_number, 0, 1) == "0") {
            $phone_number = substr($phone_number, -strlen($phone_number) + 1);
        }

        $sms = SmsVerify::orderBy('id', 'DESC')->where('phone_number', $phone_number)->first();

        $t1 = Carbon::parse($sms->updated_at);
        $t2 = Carbon::now();
        $diff = $t1->diff($t2);

        if ($diff->days == 0 && $diff->h == 0 && $diff->i <= 10) {

            if ((new UserHelper())->getUserByPhoneNumber($request->phone_number)) {

                if ($request->code == $sms->code) {
                    $user = (new UserHelper())->generateTokenByPhoneNumber($request->phone_number , $corridor);
                    return response()->json([
                        'data' => $user,
                        'errors' => [],
                        'message' => "اطلاعات کاربر با موفقیت دریافت شد"
                    ]);
                } else {
                    return response()->json([
                        'data' => null,
                        'errors' => [],
                        'message' => "کد فعال سازی اشتباه است"
                    ], 400);
                }
            } else {
                if ($request->code == $sms->code) {
                    $referral_code = (new UserHelper())->checkReferralCode($request->referral_code);
                    $user = (new UserHelper())->addUser($request->phone_number , $referral_code, '98', $corridor);
                    return response()->json([
                        'data' => $user,
                        'errors' => [],
                        'message' => "اطلاعات کاربر با موفقیت دریافت شد"
                    ]);
                } else {
                    return response()->json([
                        'data' => null,
                        'errors' => [],
                        'message' => "کد فعال سازی اشتباه است"
                    ], 400);
                }
            }

        } else {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "کد فعال سازی منقضی شده است"
            ], 400);
        }
    }

    public function sendVerifyCode(Request $request)
    {
        $messages = array(
            'phone_number.required' => 'شماره موبایل الزامی است',
            'phone_number.numeric' => 'شماره موبایل باید شامل عدد باشد',
            'prefix_code.required' => 'پیش شماره الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|numeric',
            'prefix_code' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => null
            ], 400);
        }

        $phone_number = $this->Homogenization($request->phone_number);
        $prefix_code = str_replace('+' , '' , $this->Homogenization($request->prefix_code));
        if (!$prefix_code) {
            $prefix_code = "98";
        }
        $phone_number = str_replace("+98", "", $phone_number);
        if (substr($phone_number, 0, 1) == "0") {
            $phone_number = substr($phone_number, -strlen($phone_number) + 1);
        }

        $check_verify_code = SmsVerify::where('phone_number', $phone_number)->where('prefix_code', $prefix_code)->latest()->first();
        if ($check_verify_code and $check_verify_code->created_at > Carbon::now()->subMinute()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => 'کد فعال سازی قبلا ارسال شده است. برای ارسال مجدد یک دقیقه بعد تلاش کنید.'
            ], 400);
        }

        $user = (new UserHelper())->getUserByAbsolutePhoneNumber($phone_number , $prefix_code);

        $activate_code = rand(1000, 9999);
        if ($user || $phone_number == 1234567890) {
            $type = "login";
        }else{
            $type = "register";
        }
        $smsVerify = new SmsVerify();
        $smsVerify->phone_number = $phone_number;
        $smsVerify->type = $type;
        $smsVerify->prefix_code = $prefix_code;
        if($phone_number == 1234567890 && $type == "login"){
            $smsVerify->code = 1234; //google play account
            $smsVerify->save();
            return response()->json([
                'data' =>null,
                'errors' => [],
                'message' => "کد فعال سازی با موفقیت ارسال شد"
            ]);
        }else{
            $smsVerify->code = $activate_code;
        }

        $receiver = $prefix_code == '98' ? $phone_number : '00'.$prefix_code.$phone_number;
        $url = config('kavehnegar.kavehnegar_base_url') . config('kavehnegar.kavehnegar_api_key') . config('kavehnegar.kavehnegar_lookup_url');
        try {
            Http::asForm()->post($url , [
                'receptor' => $receiver,
                'token' => $activate_code,
                'template' => 'verify'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => 'خطایی هنگام ارسال پیام رخ داده است. لطفا با پشتیبانی تماس بگیرید.'
            ], 400);
        }

        $smsVerify->save();

        return response()->json([
            'data' =>null,
            'errors' => [],
            'message' => "کد فعال سازی با موفقیت ارسال شد"
        ]);
    }

    public function checkVerifyCode(Request $request)
    {
        $messages = array(
            'phone_number.required' => 'شماره تماس الزامی است',
            'code.required' => 'کد ارسالی الزامی است',
            'phone_number.numeric' => 'شماره موبایل باید شامل عدد باشد',
            'prefix_code.required' => 'پیش شماره الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'phone_number' => 'required|numeric',
            'prefix_code' => 'required',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "بررسی کد فعال سازی شکست خورد"
            ], 400);
        }

        $phone_number = $this->Homogenization($request->phone_number);
        $prefix_code = str_replace('+' , '' , $this->Homogenization($request->prefix_code));
        if (!$prefix_code) {
            $prefix_code = "98";
        }

        if ($phone_number == 1234567890 && $request->code == 1234) { // account google play
            $user = (new UserHelper())->generateTokenByAbsolutePhoneNumber($phone_number , $prefix_code);
            return response()->json([
                'data' => $user,
                'errors' => [],
                'message' => "اطلاعات کاربر با موفقیت دریافت شد"
            ]);
        }

        $phone_number = str_replace("+98", "", $phone_number);
        if (substr($phone_number, 0, 1) == "0") {
            $phone_number = substr($phone_number, -strlen($phone_number) + 1);
        }

        $sms = SmsVerify::orderBy('id', 'DESC')->where('phone_number', $phone_number)->where('prefix_code', $prefix_code)->first();
        if (!$sms) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "اطلاعات وارد شده اشتباه است"
            ], 400);
        }

        $t1 = Carbon::parse($sms->updated_at);
        $t2 = Carbon::now();
        $diff = $t1->diff($t2);
        if ($request->corridor === 'web-app') {
            $corridor = 'web-app';
        } else {
            $corridor = 'app';
        }

        if ($diff->days == 0 && $diff->h == 0 && $diff->i <= 10) {
            if ((new UserHelper())->getUserByAbsolutePhoneNumber($phone_number , $prefix_code)) {
                if ($request->code == $sms->code) {
                    $user = (new UserHelper())->generateTokenByAbsolutePhoneNumber($phone_number , $prefix_code, $corridor);
                    return response()->json([
                        'data' => $user,
                        'errors' => [],
                        'message' => "اطلاعات کاربر با موفقیت دریافت شد"
                    ]);
                } else {
                    return response()->json([
                        'data' => null,
                        'errors' => [],
                        'message' => "کد فعال سازی اشتباه است"
                    ], 400);
                }
            } else {
                if ($request->code == $sms->code) {
                    $referral_code = (new UserHelper())->checkReferralCode($request->referral_code);
                    $user = (new UserHelper())->addUser($phone_number , $referral_code , $prefix_code, $corridor);
                    return response()->json([
                        'data' => $user,
                        'errors' => [],
                        'message' => "اطلاعات کاربر با موفقیت دریافت شد"
                    ]);
                } else {
                    return response()->json([
                        'data' => null,
                        'errors' => [],
                        'message' => "کد فعال سازی اشتباه است"
                    ], 400);
                }
            }
        } else {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "کد فعال سازی منقضی شده است"
            ], 400);
        }
    }
}

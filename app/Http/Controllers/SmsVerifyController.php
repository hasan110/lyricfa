<?php

namespace App\Http\Controllers;

use App\Models\SmsVerify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use SoapClient;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\Map;
use Illuminate\Support\Str;


class SmsVerifyController extends Controller
{
    public function sendSms(Request $request)
    {
        $messsages = array(
            'phone_number.required' => 'شماره تماس الزامی است',
            'phone_number.numeric' => 'شماره تماس باید شامل عدد باشد',
            'type.required' => 'نوع پیام الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|min:10|max:13',
            'type' => 'required',
        ], $messsages);


        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => null
            ];
            return response()->json($arr, 400);
        }

        $userId = UserController::getUserIdByPhoneNumber($request->phone_number);

        if ($request->type == "register") {
            if ($userId) {
                $arr = [
                    'data' => null,
                    'errors' => null,
                    'message' => "این کاربر قبلا ثبت نام کرده است لطفا لاگین کنید"
                ];
                return response()->json($arr, 400);
            }
        } else {
            if (!$userId) {
                $arr = [
                    'data' => null,
                    'errors' => null,
                    'message' => "این کاربر ثبت نام نکرده است لطفا ثبت نام کنید"
                ];
                return response()->json($arr, 400);
            }
        }

        // dd($request);
        $client = new SoapClient("http://ippanel.com/class/sms/wsdlservice/server.php?wsdl");
        $user = "9059509363";
        $pass = "majid123";
//        $fromNum = "+5000125475";
        $fromNum = "+3000505";
//        $fromNum = "+989000145";
        $toNum = array($request->phone_number);
        $pattern_code = "1ydwawpvjy";
        $activate_code = rand(1000, 9999);
        $input_data = array("verification-code" => $activate_code, "name" => "");
        $client->sendPatternSms($fromNum, $toNum, $user, $pass, $pattern_code, $input_data);


        $smsVerify = new SmsVerify;
        $smsVerify->phone_number = $request->phone_number;
        $smsVerify->type = $request->type;
          if( $request->phone_number == 1234567890 && $request->type == "login"){
                $smsVerify->code = 1234; //google play account
          }else{
                   $smsVerify->code = $activate_code;
          }


        $smsVerify->save();

        $arr = [
            'data' =>null,
            'errors' => [
            ],
            'message' => "کد فعال سازی با موفقیت فرستاده شد"
        ];
        return response()->json($arr, 200);

    }

    public function checkActivateCode(Request $request)
    {


        $messsages = array(
            'phone_number.required' => 'شماره تماس الزامی است',
            'code.required' => 'کد ارسالی الزامی است',

        );

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required',
            'code' => 'required'
        ], $messsages);


        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "بررسی کد فعال سازی شکست خورد"
            ];
            return response()->json($arr, 400);
        }

        if( $request->phone_number == 1234567890 && $request->code == 1234 && $request->type == "login"){ // account google play
            $user = UserController::changeTokenAndReturnUser($request->phone_number);
            $arr = [
                'data' => $user,
                'errors' => [
                ],
                'message' => "اطلاعات کاربر با موفقیت دریافت شد"
            ];
            return response()->json($arr, 200);
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


            if (UserController::getUserIdByPhoneNumber($request->phone_number)) {

                if ($request->code == $sms->code) {
                    $user = UserController::changeTokenAndReturnUser($request->phone_number);
                    $arr = [
                        'data' => $user,
                        'errors' => [
                        ],
                        'message' => "اطلاعات کاربر با موفقیت دریافت شد"
                    ];
                    return response()->json($arr, 200);
                } else {
                    $arr = [
                        'data' => null,
                        'errors' => [
                        ],
                        'message' => "کد فعال سازی اشتباه است"
                    ];
                    return response()->json($arr, 400);
                }
            } else {
                if ($request->code == $sms->code) {
                    $referral_code = UserController::checkReferralCode($request->referral_code);
                    $user = UserController::addUser($request->phone_number , $referral_code);
                    $arr = [
                        'data' => $user,
                        'errors' => [
                        ],
                        'message' => "اطلاعات کاربر با موفقیت دریافت شد"
                    ];
                    return response()->json($arr, 200);
                } else {
                    $arr = [
                        'data' => null,
                        'errors' => [
                        ],
                        'message' => "کد فعال سازی اشتباه است"
                    ];
                    return response()->json($arr, 400);
                }
            }

        } else {
            $arr = [
                'data' => null,
                'errors' => [
                ],
                'message' => "کد فعال سازی منقضی شده است"
            ];
            return response()->json($arr, 400);
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
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => null
            ];
            return response()->json($arr, 400);
        }

        $phone_number = $this->Homogenization($request->phone_number);
        $prefix_code = str_replace('+' , '' , $this->Homogenization($request->prefix_code));

        $userId = UserController::getUserIdByCPhoneNumber($phone_number , $prefix_code);

        $activate_code = rand(1000, 9999);

        if ($userId || $phone_number == 1234567890) {
            $type = "login";
        }else{
            $type = "register";
        }

        $smsVerify = new SmsVerify;
        $smsVerify->phone_number = $phone_number;
        $smsVerify->type = $type;
        $smsVerify->prefix_code = $prefix_code;
        if($phone_number == 1234567890 && $type == "login"){
            $smsVerify->code = 1234; //google play account
            $smsVerify->save();

            $arr = [
                'data' =>null,
                'errors' => [
                ],
                'message' => "کد فعال سازی با موفقیت ارسال شد"
            ];
            return response()->json($arr, 200);
        }else{
            $smsVerify->code = $activate_code;
        }

        $receiver = $prefix_code == '98' ? $phone_number : '00'.$prefix_code.$phone_number;
        $url = config('kavehnegar.kavehnegar_base_url') . config('kavehnegar.kavehnegar_api_key') . config('kavehnegar.kavehnegar_lookup_url');
        try {
            $response = Http::asForm()->post($url , [
                'receptor' => $receiver,
                'token' => $activate_code,
                'template' => 'verify'
            ]);
        } catch (Exception $e) {
            $arr = [
                'data' => null,
                'errors' => [],
                'message' => 'خطایی هنگام ارسال پیام رخ داده است. لطفا با پشتیبانی تماس بگیرید.'
            ];
            return response()->json($arr, 400);
        }

        $smsVerify->save();

        $arr = [
            'data' =>null,
            'errors' => [
            ],
            'message' => "کد فعال سازی با موفقیت ارسال شد"
        ];
        return response()->json($arr, 200);
    }

    public function checkVerifyCode(Request $request)
    {
        $messsages = array(
            'phone_number.required' => 'شماره تماس الزامی است',
            'code.required' => 'کد ارسالی الزامی است',
            'phone_number.numeric' => 'شماره موبایل باید شامل عدد باشد',
            'prefix_code.required' => 'پیش شماره الزامی است'
        );

        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'phone_number' => 'required|numeric',
            'prefix_code' => 'required',
        ], $messsages);


        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "بررسی کد فعال سازی شکست خورد"
            ];
            return response()->json($arr, 400);
        }

        $phone_number = $this->Homogenization($request->phone_number);
        $prefix_code = str_replace('+' , '' , $this->Homogenization($request->prefix_code));

        if($phone_number == 1234567890 && $request->code == 1234){ // account google play
            $user = UserController::changeTokenAndReturnUser($phone_number);
            $arr = [
                'data' => $user,
                'errors' => [
                ],
                'message' => "اطلاعات کاربر با موفقیت دریافت شد"
            ];
            return response()->json($arr, 200);
        }

        $phone_number = str_replace("+98", "", $phone_number);

        $sms = SmsVerify::orderBy('id', 'DESC')->where('phone_number', $phone_number)->where('prefix_code', $prefix_code)->first();
        if (!$sms) {
            $arr = [
                'data' => null,
                'errors' => [
                ],
                'message' => "اطلاعات وارد شده اشتباه است"
            ];
            return response()->json($arr, 400);
        }

        $t1 = Carbon::parse($sms->updated_at);
        $t2 = Carbon::now();
        $diff = $t1->diff($t2);

        if ($diff->days == 0 && $diff->h == 0 && $diff->i <= 10) {


            if (UserController::getUserIdByCPhoneNumber($phone_number , $prefix_code)) {

                if ($request->code == $sms->code) {
                    $user = UserController::changeTokenAndCReturnUser($phone_number , $prefix_code);
                    $arr = [
                        'data' => $user,
                        'errors' => [
                        ],
                        'message' => "اطلاعات کاربر با موفقیت دریافت شد"
                    ];
                    return response()->json($arr, 200);
                } else {
                    $arr = [
                        'data' => null,
                        'errors' => [
                        ],
                        'message' => "کد فعال سازی اشتباه است"
                    ];
                    return response()->json($arr, 400);
                }
            } else {
                if ($request->code == $sms->code) {
                    $referral_code = UserController::checkReferralCode($request->referral_code);
                    $user = UserController::addUser($phone_number , $referral_code , $prefix_code);
                    $arr = [
                        'data' => $user,
                        'errors' => [
                        ],
                        'message' => "اطلاعات کاربر با موفقیت دریافت شد"
                    ];
                    return response()->json($arr, 200);
                } else {
                    $arr = [
                        'data' => null,
                        'errors' => [
                        ],
                        'message' => "کد فعال سازی اشتباه است"
                    ];
                    return response()->json($arr, 400);
                }
            }

        } else {
            $arr = [
                'data' => null,
                'errors' => [
                ],
                'message' => "کد فعال سازی منقضی شده است"
            ];
            return response()->json($arr, 400);
        }

    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public static function addPayReports(Request $request)
    {

        $api_token = $request->header("ApiToken");

        $user = UserController::getUserByToken($api_token);
        if ($user) {
            $user_id = $user->id;
            $messsages = array(
                'ref_id.required' => 'ref_id نمی تواند خالی باشد',
                'id_zarin.required' => 'id_zarin نمی تواند خالی باشد',
                'id_plan.required' => 'شناسه ی پلن انتخابی نمی تواند خالی باشد'
            );

            $validator = Validator::make($request->all(), [
                'ref_id' => 'required',
                'id_zarin' => 'required',
                'id_plan' => 'required'
            ], $messsages);

            if ($validator->fails()) {
                $arr = [
                    'data' => null,
                    'errors' => $validator->errors(),
                    'message' => " شکست در وارد کردن اطلاعات",
                ];
                return response()->json($arr, 400);
            }

            $plan = SubscriptionController::getSubscriptionById($request->id_plan);

            UserController::setUserNewSubscription($request);

            $report = new Report();
            $report->user_id = $user_id;
            $report->ref_id = $request->ref_id;
            $report->val_money = $plan->amount;
            $report->id_zarin = $request->id_zarin;
            $report->description = $plan->description;
            $report->save();


            $arr = [
                'data' => null,
                'errors' => null,
                'message' => " اکانت شما با موفقیت فعال شد",
            ];
            return response()->json($arr, 200);


        } else {

            $response = [
                'data' => null,
                'errors' => [
                ],
                'message' => " مشکل در احراز هویت، درصورتی که از حساب شما کم شد و عملیاتی انجام نشد با پشتیبان تماس بگیرید",
            ];
            return response()->json($response, 401);
        }
    }
}

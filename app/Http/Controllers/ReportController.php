<?php

namespace App\Http\Controllers;

use App\Http\Helpers\UserHelper;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function addPayReports(Request $request)
    {
        $messages = array(
            'ref_id.required' => 'ref_id نمی تواند خالی باشد',
            'id_zarin.required' => 'id_zarin نمی تواند خالی باشد',
            'id_plan.required' => 'شناسه ی پلن انتخابی نمی تواند خالی باشد'
        );

        $validator = Validator::make($request->all(), [
            'ref_id' => 'required',
            'id_zarin' => 'required',
            'id_plan' => 'required'
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " شکست در وارد کردن اطلاعات",
            ], 400);
        }

        $user = (new UserHelper())->getUserByToken($request->header("ApiToken"));
        $plan = (new UserHelper())->getSubscriptionById($request->id_plan);

        if (!$user || !$plan) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "خطا هنگام دریافت اطلاعات کاربر یا پلن",
            ], 400);
        }

        (new UserHelper())->setUserSubscription($user, $plan->id);

        $report = new Report();
        $report->user_id = $user->id;
        $report->ref_id = $request->ref_id;
        $report->val_money = $plan->amount;
        $report->id_zarin = $request->id_zarin;
        $report->description = $plan->description;
        $report->save();

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => " اکانت شما با موفقیت فعال شد",
        ]);
    }
}

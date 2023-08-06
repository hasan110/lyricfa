<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LikeSingerController;
use App\Models\Report;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function addPanelReports(Request $request)
    {
            $messsages = array(
                'user_id.required' => 'user_id نمی تواند خالی باشد',
                'title.required' => 'تعداد روزهای اشتراک نمی تواند خالی باشد',
                'title.numeric' => 'تعداد روزهای اشتراک باید عدد صحیح باشد',
                'description.required' => 'دلیل اشتراک نمی تواند خالی باشد',
            );

            $validator = Validator::make($request->all(), [
                'user_id' => 'required',
                'title' => 'required|numeric',
                'description' => 'required',
            ], $messsages);

            if ($validator->fails()) {
                $arr = [
                    'data' => null,
                    'errors' => $validator->errors(),
                    'message' => " شکست در وارد کردن اطلاعات",
                ];
                return response()->json($arr, 400);
            }

           $user = UserController::getUserById($request->user_id);
            $daysSubscription = $request->title;
            $expired = Carbon::parse($user->expired_at);
            if ($expired > Carbon::now()) {
                $user->expired_at = $expired->addDays($daysSubscription);
            } else {
                $user->expired_at = Carbon::now()->addDays($daysSubscription);
            }

            unset($user['days_remain']);
            $user->save();

            $report = new Report();
            $report->user_id = $request->user_id;
            $report->ref_id = 0;
            $report->val_money = 0 ;
            $report->id_zarin = 0;
            $report->description = $request->description;
            $report->title = $request->title;
            $report->type = 1;
            $report->save();


            $arr = [
                'data' => null,
                'errors' => null,
                'message' => " اکانت کاربر با موفقیت شارژ شد",
            ];
            return response()->json($arr, 200);

    }



    public function reportsAdminList(Request $request)
    {
        $list = Report::whereNotNull('id')->where('type','!==', 0)->paginate(50);

        foreach ($list as $item) {
            unset($item['ref_id'],$item['val_money'],$item['id_zarin']);
            $item->user = UserController::getUserById($item->user_id);
        }

        $response = [
            'data' => $list,
            'errors' => [
            ],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);
    }

    public function reportsUserList(Request $request)
    {
        $list = Report::whereNotNull('id')->where('type', 0)->paginate(50);

        foreach ($list as $item) {
            unset($item['title'],$item['description']);
            $item->user = UserController::getUserById($item->user_id);
        }

        $response = [
            'data' => $list,
            'errors' => [
            ],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);
    }
}

<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LikeSingerController;
use App\Models\Report;
use App\Models\User;
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
        $list = Report::whereNotNull('id')->where('type', 0);

        if($request->sort_by){
            switch ($request->sort_by){
                case 'newest':
                default:
                    $list = $list->orderBy('id' , 'desc');
                    break;
                case 'oldest':
                    $list = $list->orderBy('id' , 'asc');
                    break;
            }
        }

        $list = $list->paginate(50);

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

    public function addGroupSubscription(Request $request)
    {
        $messsages = array(
            'hours.required' => 'تعداد ساعت تمدید اشتراک نمیتواند خالی باشد',
            'hours.numeric' => 'تعداد ساعت تمدید اشتراک باید عدد صحیح باشد',
        );

        $validator = Validator::make($request->all(), [
            'hours' => 'required|numeric'
        ], $messsages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " شکست در وارد کردن اطلاعات",
            ], 422);
        }

        $new_date = Carbon::now()->addHours($request->hours);
        $before_date = Carbon::now()->subHours($request->hours);

        User::where('expired_at' , '<' , $before_date)->update([
            'expired_at' => $new_date->format('Y-m-d H:i:s')
        ]);

        $arr = [
            'data' => null,
            'errors' => null,
            'message' => " تمدید اشتراک کاربران با موفقیت انجام شد",
        ];
        return response()->json($arr);

    }
}

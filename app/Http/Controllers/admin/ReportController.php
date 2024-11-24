<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Helpers\UserHelper;
use App\Models\Report;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

class ReportController extends Controller
{
    public function addPanelReports(Request $request)
    {
        $messages = array(
            'user_id.required' => 'user_id نمی تواند خالی باشد',
            'title.required' => 'تعداد روزهای اشتراک نمی تواند خالی باشد',
            'title.numeric' => 'تعداد روزهای اشتراک باید عدد صحیح باشد',
            'description.required' => 'دلیل اشتراک نمی تواند خالی باشد',
            'type.required' => 'نوع اعمال نمی تواند خالی باشد',
            'val_money.required_if' => 'مقدار پرداختی الزامی است',
            'ref_id.required_if' => 'کد درگاه / پیگیری الزامی است',
        );

        $validator = Validator::make($request->all(), [
            'user_id' => 'required',
            'title' => 'required|numeric',
            'description' => 'required',
            'type' => 'required',
            'val_money' => 'required_if:type,0',
            'ref_id' => 'required_if:type,0',
        ], $messages);

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " لطفا تمامی فیلد ها را به دقت پر کنید",
            ], 400);
        }

        $user = (new UserHelper())->getUserById($request->user_id);
        $daysSubscription = $request->title;
        $expired = Carbon::parse($user->expired_at);
        if ($expired > Carbon::now()) {
            $user->expired_at = $expired->addDays($daysSubscription);
        } else {
            $user->expired_at = Carbon::now()->addDays($daysSubscription);
        }

        $user->save();

        $report = new Report();
        $report->user_id = $request->user_id;
        $report->ref_id = $request->ref_id;
        $report->val_money = $request->val_money;
        $report->id_zarin = 0;
        $report->description = $request->description;
        $report->title = null;
        $report->type = $request->type;
        $report->save();

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => " اکانت کاربر با موفقیت شارژ شد",
        ]);
    }

    public function reportsAdminList(Request $request)
    {
        $list = Report::whereNotNull('id')->where('type','!==', 0)->paginate(50);

        foreach ($list as $item) {
            unset($item['ref_id'],$item['val_money'],$item['id_zarin']);
            $item->user = (new UserHelper())->getUserById($item->user_id);
            $item->persian_created_at = Jalalian::forge($item->created_at)->format('%Y-%m-%d H:i');
        }

        return response()->json([
            'data' => $list,
            'errors' => [],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
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
            $item->user = (new UserHelper())->getUserById($item->user_id);
            $item->persian_created_at = Jalalian::forge($item->created_at)->format('%Y-%m-%d H:i');
        }

        return response()->json([
            'data' => $list,
            'errors' => [
            ],
            'message' => "اطلاعات با موفقیت گرفته شد",
        ]);
    }

    public function addGroupSubscription(Request $request)
    {
        $messages = array(
            'hours.required' => 'تعداد ساعت تمدید اشتراک نمیتواند خالی باشد',
            'hours.numeric' => 'تعداد ساعت تمدید اشتراک باید عدد صحیح باشد',
        );

        $validator = Validator::make($request->all(), [
            'hours' => 'required|numeric'
        ], $messages);

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

        return response()->json([
            'data' => null,
            'errors' => null,
            'message' => " تمدید اشتراک کاربران با موفقیت انجام شد",
        ]);
    }
}

<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\OrderMusic;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

class OrderMusicController extends Controller
{
    public function editOrderMusic(Request $request)
    {
        $messages = array(
            'id.required' => 'id سفارش را وارد کنید',
            'id.numeric' => 'id سفارش عدد هست',
            'condition_order.required' => 'وضعیت سفارش را وارد کنید',
            'condition_order.numeric' => 'وضعیت سفارش عدد هست'
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
            'condition_order' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  ویرایش سفارش آهنگ شکست خورد",
            ];
            return response()->json($arr, 400);
        }


        $order = $this->getOrderById($request->id);
        if(isset($order)){
            $order->condition_order = $request->condition_order;
            $order->save();
            $user = UserController::getUserById($order->user_id);
            try {
                if ($request->notification_title && $request->notification_text && ($user && $user->fcm_refresh_token !== '')) {
                    $url = "https://fcm.googleapis.com/fcm/send";
                    //serverKey
                    $apiKey = "AAAABTTSEFI:APA91bHnEDLP8s_WQQw-cMm7Rf7NsGtquWDT3JJPLnxDUBCJJUV3fvLbgQ5fAD4mh0TZOW77WnjVKLUnFlGxxk9wObBRFSl-9vnBcLUFwJQC-LaG4nhgq8LG6_tvtiMcmz0-ILAaskPd";
                    $headers = array(
                        'Authorization:key=' . $apiKey,
                        'Content-Type: application/json'
                    );

                    $notifBody = [
                        'notification' => [
                            'title' => $request->notification_title,
                            'body' => $request->notification_text,
                            'image' => ''
                        ],
                        'data' => [
                            'to' => 'VIP',
                            'date' => Carbon::now(),
                            'other_data' => 'not important',
                            "sound" => "default"
                        ],
                        'time_to_live' => 3600,
                        'to' => $user->fcm_refresh_token
                    ];
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notifBody));

                    //Execute
                    curl_exec($ch);
                    curl_close($ch);
                }
            } catch (\Exception $exception) {
                $error = $exception->getMessage();
                Log::error($error);
            }

            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "سفارش آهنگ شما با موفقیت به روز شد.",
            ];

            return response()->json($arr, 200);
        }else {
            $arr = [
                'data' => null,
                'errors' => null,
                'message' => "خطا در به روز رسانی.",
            ];
            return response()->json($arr, 400);
        }
    }

    public function ordersList(Request $request)
    {
        $orders = OrderMusic::with('user')->where('condition_order', 0)->paginate(1000);

        $now = Carbon::now();
        foreach ($orders as $item) {
            $expiredAt = $item->user->expired_at;
            $diff = $now->diffInDays($expiredAt);
            if ($now > $expiredAt) {
                $subscription_days_remain = 0;
            } else {
                $subscription_days_remain = $diff;
            }

            $order_created_spent_days = $now->diffInDays(Carbon::parse($item->created_at));

            $item->rate = $subscription_days_remain + $order_created_spent_days;
            $item->persian_created_at = Jalalian::forge($item->created_at)->format('%Y-%m-%d H:i');
        }

        $sortedResult = $orders->getCollection()->sortByDesc('rate')->values();
        $orders->setCollection($sortedResult);

        $response = [
            'data' => $orders,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response);
    }

    /*
     * condition_order 0 -> بررسی شود
     * 1 -> آهنگ اضافه شد
     * 2 -> آهنگ اضافه نشد
     * 3 -> آهنگ در لیست آهنگ های قبلی هست
     */
    private function getOrderById($id)
    {
        return OrderMusic::where('id', $id)->first();
    }

    public function getOrder(Request $request)
    {
        $messages = array(
            'id.required' => 'id سفارش را وارد کنید',
            'id.numeric' => 'id سفارش عدد هست',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            $arr = [
                'data' => null,
                'errors' => $validator->errors(),
                'message' => "  ویرایش سفارش آهنگ شکست خورد",
            ];
            return response()->json($arr, 400);
        }

        $order =  OrderMusic::where('id', $request->id)->first();
        $order->user = UserController::getUserById($order->user_id);

        $response = [
            'data' => $order,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response);
    }
}

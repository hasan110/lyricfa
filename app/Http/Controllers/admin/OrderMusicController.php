<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\LikeSingerController;
use App\Models\OrderMusic;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class OrderMusicController extends Controller
{

    public function editOrderMusic(Request $request)
    {


            $messsages = array(
                'id.required' => 'id سفارش را وارد کنید',
                'id.numeric' => 'id سفارش عدد هست',
                'condition_order.required' => 'وضعیت سفارش را وارد کنید',
                'condition_order.numeric' => 'وضعیت سفارش عدد هست'
            );

            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric',
                'condition_order' => 'required|numeric',
            ], $messsages);

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
        }

        $sortedResult = $orders->getCollection()->sortByDesc('rate')->values();
        $orders->setCollection($sortedResult);

        $response = [
            'data' => $orders,
            'errors' => null,
            'message' => "اطلاعات با موفقیت گرفته شد",
        ];
        return response()->json($response, 200);
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


        $messsages = array(
            'id.required' => 'id سفارش را وارد کنید',
            'id.numeric' => 'id سفارش عدد هست',
        );

        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
        ], $messsages);

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
        return response()->json($response, 200);
    }
}

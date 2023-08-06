<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{

    public static function getSubscriptionById($id_plan)
    {

        $subscriptions = Subscription::where('id', $id_plan)->first();
        return $subscriptions;

    }

    public function getSubscriptions(Request $request)
    {


        $subscriptions = Subscription::get();
        if ($subscriptions) {
            $response = [
                'data' => $subscriptions,
                'errors' => [
                ],
                'message' => "اطلاعات با موفقیت گرفته شد",
            ];
            return response()->json($response, 200);
        } else {
            $response = [
                'data' => null,
                'errors' => [
                ],
                'message' => "هیچ پلنی وجود ندارد",
            ];
            return response()->json($response, 400);
        }

    }


}

<?php

namespace App\Http\Controllers;

use App\Models\Subscription;

class SubscriptionController extends Controller
{
    public function getSubscriptions()
    {
        $subscriptions = Subscription::get();
        if ($subscriptions) {
            return response()->json([
                'data' => $subscriptions,
                'errors' => [],
                'message' => "اطلاعات با موفقیت گرفته شد",
            ]);
        } else {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "هیچ پلنی وجود ندارد",
            ], 400);
        }
    }
}

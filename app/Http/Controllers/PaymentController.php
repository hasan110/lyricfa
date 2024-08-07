<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Exception;

class PaymentController extends Controller
{
    public function createSubscriptionPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'required',
        ], array(
            'plan_id.required' => 'شناسه پلن نمی تواند خالی باشد'
        ));

        if ($validator->fails()) {
            return response()->json([
                'data' => null,
                'errors' => $validator->errors(),
                'message' => " شکست در وارد کردن اطلاعات",
            ], 400);
        }

        $subscription = Subscription::find($request->input('plan_id'));
        $callback_url = $request->input('callback_url') ?? null;

        if (!$subscription) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "پلن یافت نشد.",
            ], 400);
        }

        $api_token = $request->header("ApiToken");
        $user = UserController::getUserByToken($api_token);

        try {
            $url = Payment::doPayment($user, Payment::PAYMENT_TYPE_SUBSCRIPTION,$subscription->id, $subscription->amount, $callback_url);
        } catch (Exception $e) {
            return response()->json([
                'data' => null,
                'errors' => [],
                'message' => "خطا هنگام برقراری ارتباط با درگاه",
            ], 400);
        }

        return response()->json([
            'data' => ['url' => $url],
            'errors' => [],
            'message' => "در انتظار پرداخت ..."
        ]);
    }

}

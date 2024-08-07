<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\UserController;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Morilog\Jalali\Jalalian;

class PaymentController extends Controller
{
    public function verifyPayment(Request $request)
    {
        $token = $request->token;

        $payment = Payment::where('token', $token)->first();
        if (!$payment) {
            $data = [
                'valid' => 0,
                'status' => 0,
                'message' => 'توکن نامعتبر است.',
            ];
            return view('payment' , compact('data'));
        }

        if ($payment->status != 0) {
            $data = [
                'valid' => 0,
                'status' => 0,
                'message' => 'این پرداخت قبلا تعیین تکلیف شده است.',
            ];
            return view('payment' , compact('data'));
        }

        if ($payment->type == 'subscription') {
            $reason = 'خرید اشتراک';
        } else {
            $reason = 'پرداخت';
        }
        $date = Jalalian::forge($payment->updated_at)->addMinutes(3.5*60)->format('%Y-%m-%d H:i');

        if ($request->Status != 'OK') {
            $payment->status = 2;
            $payment->save();

            $data = [
                'valid' => 1,
                'status' => 2,
                'status_text' => 'ناموفق',
                'amount' => $payment->amount,
                'reason' => $reason,
                'authority' => $payment->authority,
                'callback' => $payment->callback_url,
                'tracking_code' => null,
                'date' => $date,
            ];
            return view('payment' , compact('data'));
        }

        try {
            $result = Payment::verifyPayment($payment);

            $payment->tracking_code = $result;
            $payment->status = 1;
            $payment->save();

        } catch (Exception $e) {
            $payment->status = 2;
            $payment->save();

            $data = [
                'valid' => 1,
                'status' => 2,
                'status_text' => 'ناموفق',
                'amount' => $payment->amount,
                'reason' => $reason,
                'authority' => $payment->authority,
                'description' => 'خطا هنگام تایید تراکنش !!! درصورت کسر وجه از حساب، حداکثر تا 72 ساعت آینده مبلغ کسر شده به حساب شما باز خواهد گشت.',
                'callback' => $payment->callback_url,
                'tracking_code' => null,
                'date' => $date,
            ];
            return view('payment' , compact('data'));
        }

        $data = [
            'valid' => 1,
            'status' => 1,
            'status_text' => 'موفق',
            'amount' => $payment->amount,
            'reason' => $reason,
            'authority' => $payment->authority,
            'callback' => $payment->callback_url,
            'tracking_code' => $payment->tracking_code,
            'date' => $date,
        ];
        return view('payment' , compact('data'));
    }

}

<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
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
        $link_status = '';

        $payment_status = false;

        if ($payment->gateway == 'zarinpal') {
            $payment_status = $request->Status === 'OK';
        }
        if ($payment->gateway == 'zibal') {
            $payment_status = $request->success == 1;
        }

        if (!$payment_status) {
            $payment->status = 2;
            $payment->save();
            if ($payment->callback_url == 'http://liricfa/') {
                $link_status = 'fail';
            }

            $data = [
                'valid' => 1,
                'status' => 2,
                'status_text' => 'ناموفق',
                'amount' => $payment->amount,
                'reason' => $reason,
                'authority' => $payment->authority,
                'callback' => $payment->callback_url.$link_status,
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
            if ($payment->callback_url == 'http://liricfa/') {
                $link_status = 'fail';
            }

            $data = [
                'valid' => 1,
                'status' => 2,
                'status_text' => 'ناموفق',
                'amount' => $payment->amount,
                'reason' => $reason,
                'authority' => $payment->authority,
                'description' => 'خطا هنگام تایید تراکنش !!! درصورت کسر وجه از حساب، حداکثر تا 72 ساعت آینده مبلغ کسر شده به حساب شما باز خواهد گشت.',
                'callback' => $payment->callback_url.$link_status,
                'tracking_code' => null,
                'date' => $date,
            ];
            return view('payment' , compact('data'));
        }

        if ($payment->callback_url == 'http://liricfa/') {
            $link_status = 'success';
        }

        $data = [
            'valid' => 1,
            'status' => 1,
            'status_text' => 'موفق',
            'amount' => $payment->amount,
            'reason' => $reason,
            'authority' => $payment->authority,
            'callback' => $payment->callback_url.$link_status,
            'tracking_code' => $payment->tracking_code,
            'date' => $date,
        ];
        return view('payment' , compact('data'));
    }

}

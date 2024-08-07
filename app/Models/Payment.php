<?php

namespace App\Models;

use App\Http\Controllers\UserController;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public const PAYMENT_TYPE_SUBSCRIPTION = 'subscription';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function doPayment($user, $type, $paymentable_id, $amount, $callback)
    {
        $merchant_id = Setting::fetch()['zarinpal_merchent_id'];
        $token = Str::random(30);

        $payment = new Payment();
        $payment->user_id = $user->id;
        $payment->type = $type;
        $payment->paymentable_id = $paymentable_id;
        $payment->amount = $amount;
        $payment->token = $token;
        $payment->callback_url = $callback;
        try {
            $req = Http::post('https://payment.zarinpal.com/pg/v4/payment/request.json' , [
                "merchant_id" => $merchant_id,
                "amount" => $amount*10,
                "callback_url" => route('payment.verify' , ['token' => $token]),
                "description" => "خرید اشتراک لیریکفا",
                "metadata" => [
                    "mobile" => $user->phone_number,
                    "email" => ""
                ]
            ]);
            $data = $req->json();
            if ($data['data']['code'] !== 100) {
                Log::error($data['data']['message']);
                throw new Exception('خطا هنگام ایجاد تراکنش در درگاه پرداخت');
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        $authority = $data['data']['authority'];

        $payment->authority = $authority;
        $payment->save();

        return 'https://payment.zarinpal.com/pg/StartPay/' . $authority;
    }

    public static function verifyPayment($payment)
    {
        $merchant_id = Setting::fetch()['zarinpal_merchent_id'];

        try {
            $req = Http::post('https://payment.zarinpal.com/pg/v4/payment/verify.json' , [
                "merchant_id" => $merchant_id,
                "amount" => $payment->amount*10,
                "authority" => $payment->authority,
            ]);
            $data = $req->json();
            if ($data['data']['code'] !== 100 && $data['data']['code'] !== 101) {
                Log::error($data['data']['message']);
                throw new Exception('خطا هنگام ایجاد تراکنش در درگاه پرداخت');
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        $ref_id = $data['data']['ref_id'];

        if ($payment->type == self::PAYMENT_TYPE_SUBSCRIPTION) {
            $subscription = Subscription::find($payment->paymentable_id);
            if ($subscription) {
                UserController::addSubscription($payment->user_id, $subscription->id);

                $report = new Report();
                $report->user_id = $payment->user_id;
                $report->ref_id = $ref_id;
                $report->val_money = $payment->amount;
                $report->id_zarin = $merchant_id;
                $report->description = $subscription->description;
                $report->save();
            }
        }

        return $ref_id;
    }
}

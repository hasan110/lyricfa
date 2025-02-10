<?php

namespace App\Models;

use App\Http\Helpers\UserHelper;
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
    public const PAYMENT_GATEWAYS = ['zarinpal','zibal'];
    public const DEFAULT_PAYMENT_GATEWAY = 'zarinpal';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function doPayment($user, $type, $paymentable_id, $amount, $callback)
    {
        $setting = Setting::fetch();
        $token = Str::random(30);
        $gateway = self::getGateway($setting['default_gateway']);

        $payment = new Payment();
        $payment->user_id = $user->id;
        $payment->type = $type;
        $payment->gateway = $gateway;
        $payment->paymentable_id = $paymentable_id;
        $payment->amount = $amount;
        $payment->token = $token;
        $payment->callback_url = $callback;

        if ($gateway == 'zarinpal') {
            try {
                $metadata = [];
                if ($user->phone_number) {
                    $metadata['mobile'] = $user->phone_number;
                }
                if ($user->email) {
                    $metadata['email'] = $user->email;
                }
                $req = Http::post('https://payment.zarinpal.com/pg/v4/payment/request.json' , [
                    "merchant_id" => $setting['zarinpal_merchent_id'],
                    "amount" => $amount*10,
                    "callback_url" => route('payment.verify' , ['token' => $token]),
                    "description" => "خرید اشتراک لیریکفا",
                    "metadata" => $metadata
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
            $payment->authority = (string)$authority;
            $payment->save();
            return 'https://payment.zarinpal.com/pg/StartPay/' . $authority;

        } else if ($gateway == 'zibal') {
            try {
                $data = [
                    "merchant" => $setting['zibal_merchent_id'],
                    "amount" => $amount*10,
                    "callbackUrl" => route('payment.verify' , ['token' => $token]),
                    "description" => "خرید اشتراک لیریکفا"
                ];
                if ($user->phone_number) {
                    $data['mobile'] = $user->phone_number;
                }

                $req = Http::post('https://gateway.zibal.ir/v1/request' , $data);
                $data = $req->json();
                if ($data['result'] !== 100) {
                    Log::error($data['message']);
                    throw new Exception('خطا هنگام ایجاد تراکنش در درگاه پرداخت');
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

            $authority = $data['trackId'];
            $payment->authority = (string)$authority;
            $payment->save();
            return 'https://gateway.zibal.ir/start/' . $authority;

        } else {
            throw new Exception('خطایی هنگام اتصال به درگاه رخ داده است');
        }
    }

    public static function verifyPayment($payment)
    {
        $setting = Setting::fetch();

        if ($payment->gateway == 'zarinpal') {
            try {
                $req = Http::post('https://payment.zarinpal.com/pg/v4/payment/verify.json' , [
                    "merchant_id" => $setting['zarinpal_merchent_id'],
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
            $id_zarin = $setting['zarinpal_merchent_id'];

        } else if ($payment->gateway == 'zibal') {
            try {
                $req = Http::post('https://gateway.zibal.ir/v1/verify' , [
                    "merchant" => $setting['zibal_merchent_id'],
                    "trackId" => $payment->authority,
                ]);
                $data = $req->json();
                if ($data['result'] !== 100) {
                    Log::error($data['message']);
                    throw new Exception('خطا هنگام تایید تراکنش در درگاه پرداخت');
                }
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }

            $ref_id = $data['refNumber'];
            $id_zarin = $setting['zibal_merchent_id'];
        } else {
            throw new Exception('نوع درگاه نامعتبر است. با پشتیبانی تماس بگیرید');
        }

        if ($payment->type == self::PAYMENT_TYPE_SUBSCRIPTION) {
            $subscription = Subscription::find($payment->paymentable_id);
            if ($subscription) {
                $user = (new UserHelper())->getUserById($payment->user_id);
                (new UserHelper())->setUserSubscription($user, $subscription->id);

                $report = new Report();
                $report->user_id = $payment->user_id;
                $report->ref_id = (string)$ref_id;
                $report->val_money = $payment->amount;
                $report->id_zarin = $id_zarin;
                $report->description = $subscription->description;
                $report->save();
            }
        }

        return $ref_id;
    }

    public static function getGateway($gateway) :string
    {
        if (in_array($gateway, self::PAYMENT_GATEWAYS)) {
            return $gateway;
        }
        return self::DEFAULT_PAYMENT_GATEWAY;
    }
}

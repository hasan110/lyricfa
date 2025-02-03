<?php

namespace App\Mail;

use App\Models\SmsVerify;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendVerifyCode extends Mailable
{
    use Queueable, SerializesModels;

    public $verifyCode;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(SmsVerify $verifyCode)
    {
        $this->verifyCode = $verifyCode;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('mails.send_verify_code');
    }
}

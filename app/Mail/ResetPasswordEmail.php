<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ResetPasswordEmail extends Mailable
{
    use Queueable, SerializesModels;


    public $token;
    public $lang;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($token, $lang)
    {
        $this->token = $token;
        $this->lang = $lang;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.resetPasswordEmail')->from($address = config('email_config')['notification']['from'], $name = env('APP_NAME'))
            ->subject(trans('custom.forgot_your_password',[],$this->lang));
    }
}

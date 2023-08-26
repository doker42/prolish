<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyNewEmailMail extends Mailable
{
    use Queueable, SerializesModels;

    public $verify_new_mail;
    public $lang;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($verify_new_mail)
    {
        $this->verify_new_mail = $verify_new_mail;
        $this->lang = User::find($verify_new_mail->user_id)->locale;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.verifyNewEmail')->from($address = config('email_config')['notification']['from'], $name = env('APP_NAME'))
            ->subject(trans('custom.new_email_validation.new_email_verification', [], $this->lang));
    }
}

<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AdminNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $opts;
    public $lang;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $opts)
    {
        $this->opts = $opts;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->lang = $this->opts['lang'];
        return $this->view('emails.adminNotification')->from($address = config('email_config')['notification']['from'], $name = env('APP_NAME'))
            ->subject(trans('custom.company_verification.you_have_new_notification_from_admin',[],$this->opts['lang']));
    }
}

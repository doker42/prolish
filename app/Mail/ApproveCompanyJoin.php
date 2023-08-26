<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ApproveCompanyJoin extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $lang;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $lang)
    {
        $this->user = $user;
        $this->lang = $lang;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.approveCompanyJoin')->from($address = config('email_config')['notification']['from'], $name = env('APP_NAME'))
            ->subject(trans('custom.company_join_validation.request_to_join_your_company',[],$this->lang));
    }
}

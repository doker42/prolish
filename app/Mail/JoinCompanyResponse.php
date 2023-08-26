<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class JoinCompanyResponse extends Mailable
{
    use Queueable, SerializesModels;

    public $action;
    public $company;
    public $lang;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($action, $company, $lang)
    {
        $this->action = $action;
        $this->company = $company;
        $this->lang = $lang;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $theme_template = $this->action == 'approve'? 'custom.company_join_validation.your_request_to_join_approved':'custom.company_join_validation.your_request_to_join_declined';
        return $this->view('emails.joinCompanyResponse')->from($address = config('email_config')['notification']['from'], env('APP_NAME'))
            ->subject(trans($theme_template,['company_name' => $this->company],$this->lang));
    }
}

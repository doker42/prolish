<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EntityAccessDenied extends Mailable
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

        $theme = '';
        if($this->opts['entity'] == 'project'){
            $theme = trans('custom.the_administrator_of_the_project_closed_access',['project_name'=> $this->opts['project_name']],$this->opts['lang']);
        }
        if($this->opts['entity'] == 'company'){
            $theme = trans('custom.the_administrator_of_the_company_closed_access',['company_title'=> $this->opts['company_title']],$this->opts['lang']);
        }
        return $this->view('emails.entityAccessDenied')->from($address = config('email_config')['notification']['from'], env('APP_NAME'))
            ->subject($theme);
    }
}

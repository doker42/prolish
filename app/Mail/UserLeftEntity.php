<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserLeftEntity extends Mailable
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

        $theme = '';
        if($this->opts['entity'] == 'project'){
            $theme = trans('custom.user_has_left_the_project',['user_name'=> $this->opts['user_name'],'project_name' => $this->opts['project_name']],$this->opts['lang']);
        }
        if($this->opts['entity'] == 'company'){
            $theme = trans('custom.user_has_left_the_company',['user_name'=> $this->opts['user_name'], 'company_title' => $this->opts['company_title']],$this->opts['lang']);
        }


        $this->lang = $this->opts['lang'];
        return $this->view('emails.userLeftEntity')->from($address = config('email_config')['notification']['from'], env('APP_NAME'))
            ->subject($theme);
    }
}

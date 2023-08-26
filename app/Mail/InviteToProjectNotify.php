<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InviteToProjectNotify extends Mailable
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
        return $this->view('emails.inviteToProjectNotify')->from($address = config('email_config')['notification']['from'], $name = env('APP_NAME'))
            ->subject(trans('custom.youve_been_invited_project',['project_name' => $this->opts['project_title']],$this->opts['lang']));
    }
}

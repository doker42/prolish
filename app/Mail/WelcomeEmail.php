<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class WelcomeEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $lang;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->lang = $this->user->locale;

        dd($this->lang);

        return $this->view('emails.welcomeEmail')->from($address = config('email_config')['welcome']['from'], $name = env('APP_NAME'))
            ->subject(trans('custom.welcome_to_my3dcloud', [], $this->user->locale));
    }
}

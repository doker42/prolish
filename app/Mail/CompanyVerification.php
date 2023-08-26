<?php
declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyVerification extends Mailable
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

        switch ($this->opts['type']) {
            case 'request':
                $email_theme = 'custom.company_verification.new_verification_company_request';
                $this->opts['lang'] = 'en';
                break;

            case 'approved':
                $email_theme = 'custom.company_verification.company_verification_been_approved';
                break;

            case 'cancelled':
                $email_theme = 'custom.company_verification.company_verification_been_cancelled';
                break;
        }
        $this->lang = $this->opts['lang'];
        return $this->view('emails.companyVerification')->from($address = config('email_config')['notification']['from'], $name = env('APP_NAME'))
            ->subject(trans($email_theme, ['company_name' => $this->opts['company_title']], $this->opts['lang']));
    }
}
<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class CompanyStorageInviteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $lang;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($user, $company)
    {
        $this->user = $user;
        $this->company = $company;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $nickName = env('PROJECT_DIR') . $this->company->id;

        $data = [
            'user' => $this->user,
            'link' => env('NEXTCLOUD_API_URL') . "/remote.php/dav/files/$nickName/",
            'nickName' => $nickName,
            'password' => $this->company->storage_pass
        ];

        $this->lang = $this->user->locale;
        $this->link = $data['link'];
        $this->nickName = $data['nickName'];
        $this->password = $data['password'];
        return $this->view('emails.storageCreds', $data)->from($address = config('email_config')['notification']['from'], $name = env('APP_NAME'))
            ->subject(trans('custom.we_send_you_creds'));
    }
}

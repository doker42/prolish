<?php

namespace App\Jobs;

use App\Models\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class CompanyStorageCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user;
    public $company;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user, $company)
    {
        $this->user = $user;
        $this->company = $company;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {

            Company::manager()->initiateNextCloud($this->company, true);

            Mail::to($this->user->email)->send(new \App\Mail\CompanyStorageInviteMail($this->user,$this->company));

        } catch (\Exception $e) {
            Log::info('Failed to create NEXTCloud account ' . $e->getMessage());
        }
    }
}

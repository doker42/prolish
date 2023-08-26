<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use App\Models\UserSettings;
use App\Models\VerifyNewEmail;
use App\Models\VerifyUser;
use Carbon\Carbon;
use Illuminate\Console\Command;

class VerifyTokensLifetime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tokenlifetime:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        /*checking three days  ago */
        $days3_ago = Carbon::now()->subDay(3);

        $not_verified_users = User::withTrashed()->where('verified', User::VERIFIED_NOT_VERIFIED)->where('created_at', '<', $days3_ago)->get();

        foreach($not_verified_users as $user){
            VerifyUser::where('user_id', $user->id)->delete();
            UserSettings::where('user_id', $user->id)->delete();
            $company = Company::where('owner_id', $user->id)->first();
            $company->forceDelete();
            $user->forceDelete();

        }

        /*checking five days  ago */
        $days5_ago = Carbon::now()->subDay(5);

        $not_aprooved_users = User::withTrashed()->where('verified', User::VERIFIED_AWAITS_APPROVE)->where('created_at', '<', $days5_ago)->get();

        foreach($not_aprooved_users as $user){
            VerifyUser::where('user_id', $user->id)->delete();
            UserSettings::where('user_id', $user->id)->delete();
            $user->company_id = null;
            $user->save();
            $user->forceDelete();
        }

        /*checking day ago*/
        $day_ago = Carbon::now()->subDay(1);

        $not_used_virify_email_token = VerifyNewEmail::where('created_at', '<', $day_ago)->get();

        foreach($not_used_virify_email_token as $token){
            VerifyNewEmail::where('user_id', $token->user_id)->delete();
        }


    }
}

<?php

namespace App\Console\Commands;

use App\Mail\PaymentReminder;
use App\Mail\TrialEndsReminder;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Membership;
use Illuminate\Console\Command;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class MembershipPeriodsReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'membershipperiod:remind';

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

        /*checking after week */
        $sevendays_next = Carbon::now()->addDay(7);
        $sevendays_next_minus_hour = Carbon::now()->addDay(7)->addHour(1);

        $companies = Company::withTrashed()->whereNotNull('active_until')->whereNull('deleted_at')->where('active_until', '>=', $sevendays_next)->where('active_until', '<', $sevendays_next_minus_hour)->get();

        foreach($companies as $company){

            $active_untill = Carbon::parse($company->active_until)->format('Y-m-d');

            if($company->membership->id == Membership::FREE_PACKAGE_ID){
                Mail::to($company->owner->email)->send(new TrialEndsReminder([
                    'lang' => $company->owner->locale,
                    'date' => $active_untill,
                    'user_name' => $company->owner->name]));
            } else {
                $summ = 0;
                $last_invoice = Invoice::where('company_id', $company->id)->where('payment_status', 'custom.payment_paid')->latest()->first();

                if(!empty($last_invoice)){
                    $summ = $last_invoice->price == $company->membership->month_price?$company->membership->month_price:$company->membership->year_price;
                }

                if ($summ == 0){
                    $summ = $company->membership->year_price;
                }

                Mail::to($company->owner->email)->send(new PaymentReminder([
                    'lang' => $company->owner->locale,
                    'date' => $active_untill,
                    'user_name' => $company->owner->name,
                    'tarif_name' => $company->membership->title,
                    'summ' => '€'.$summ]));
            }

        }



    }
}

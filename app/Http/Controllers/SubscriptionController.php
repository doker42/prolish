<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\CompanySubscriptions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{

    /*
     * Returns user's active subscriptions
     */
    public function getActive()
    {
        if (Auth::check()) {
            $company = Company::where('id', Auth::user()->company_id)->first();
            if (!empty($company) && $company->owner_id == Auth::user()->id) {
                $active_subscriptions_ids = Invoice::where('company_id', $company->id)->where('payment_status', Invoice::STATUS_SUCCEEDED)->pluck('recurring_id');
                $subscriptions =  CompanySubscriptions::where('company_id', $company->id)->whereIn('id', $active_subscriptions_ids)->get();
                foreach($subscriptions as $item){
                    $item->ends_at = Carbon::parse($item->ends_at)->format('Y-m-d');
                }
                return $subscriptions;
            }
            return response()->json(['errors'=>'To see subscription user should be an owner'], 401);
        }
    }

    /* Unsubscribe user from subscription
     *
     */
    public function unsubscribe(Request $request)
    {
        $subscription = CompanySubscriptions::where('id',$request->get('id'))->first();
        if (!empty($subscription)){
            if(CompanySubscriptions::manager()->delete($subscription->id)){
                return ['message' => 'success'];
            }
        }
        return response()->json(['errors'=>'Unable to unsubscribe'], 401);
    }
}

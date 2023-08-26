<?php

namespace App\Http\Controllers;

use App\Mail\CommonEmail;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Membership;
use App\Models\CompanySubscriptions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\In;
use Laravel\Cashier\Http\Controllers\WebhookController as CashierController;
use Illuminate\Support\Facades\Log;

class WebhookController extends CashierController
{
    public function handleInvoicePaid(array $params):array
    {
        Log::channel('stripehooklog')->info($params);
        $company = Company::where('stripe_id', $params['data']['object']['customer'])->first();
        if (!empty($company)) {
            $subscription = CompanySubscriptions::where('stripe_id',$params['data']['object']['subscription'])->first();
            if(!empty($subscription)){
                if($params['data']['object']['billing_reason'] == 'subscription_create'){
                    $invoice = Invoice::where('stripe_id', $subscription->stripe_id.'_initial')->first();
                    if(!empty($invoice)){
                        $invoice->stripe_id = $params['data']['object']['payment_intent'];
                        $invoice->payment_status = Invoice::STATUS_SUCCEEDED;
                        $invoice->stripe_url = $params['data']['object']['hosted_invoice_url'];
                        $invoice->save();
                        $membership = Membership::where('year_stripe_sub_key', $subscription->stripe_plan)->orWhere('month_stripe_sub_key', $subscription->stripe_plan)->first();
                        if(!empty($membership)){
                            $date = Carbon::now();
                            if($membership->year_stripe_sub_key == $subscription->stripe_plan){
                                $period = $date->addYear();
                            } else{
                                $period = $date->addMonth();
                            }
                            $company->active_until = $period;
                            $company->membership_id = $membership->id;
                            $subscription->ends_at = $period;
                            $company->save();
                            $subscription->save();
                        } else {
                            $message = 'Missing membership for stripe invoice ' . $params['data']['object']['id'] . ' been paid by undefined customer ' . $params['data']['object']['customer'];
                            $this->notifyAdmin($message);
                        }
                    } else {
                        $message = 'Missing system invoice for stripe invoice ' . $params['data']['object']['id'] . ' been paid by undefined customer ' . $params['data']['object']['customer'];
                        $this->notifyAdmin($message);
                    }

                    $subscriptions = CompanySubscriptions::where('company_id', $company->id)->where('id', '!=', $subscription->id)->get();
                    foreach ($subscriptions as $old_subscription) {
                        if (!CompanySubscriptions::manager()->delete($old_subscription->id)) {
                            $message = 'Failed to cancel Subscription ' . $params['data']['object']['subscription'] . ' of  customer ' . $params['data']['object']['customer'];
                            $this->notifyAdmin($message);
                        }
                    }

                } else {
                    $membership = Membership::where('year_stripe_sub_key', $subscription->stripe_plan)->orWhere('month_stripe_sub_key', $subscription->stripe_plan)->first();
                    if (!empty($membership)) {
                        $exist_invoice = Invoice::where('stripe_id', $params['data']['object']['payment_intent'])->first();
                        if(!empty($exist_invoice)){
                            $exist_invoice->payment_status = Invoice::STATUS_SUCCEEDED;
                            $exist_invoice->save();
                            $this->notifyAdmin('Detected webhook for existing invoice '. $params['data']['object']['id'].
                                ' and with payment id '.$params['data']['object']['payment_intent']. ' and system id '.$exist_invoice->id);
                        } else {
                            $new_invoice = Invoice::create([
                                'title' => Invoice::manager()->generateInvoiceID(),
                                'price' => $membership->year_stripe_sub_key == $subscription->stripe_plan ? $membership->year_price : $membership->month_price,
                                'stripe_id' => $params['data']['object']['payment_intent'],
                                'payment_status' => Invoice::STATUS_SUCCEEDED,
                                'membership_id' => $membership->id,
                                'company_id' => $company->id,
                            ]);
                            $new_invoice->stripe_url =  $params['data']['object']['hosted_invoice_url'];
                            $new_invoice->recurring_id = $subscription->id;
                            $new_invoice->save();
                            $date = Carbon::now();
                            if ($membership->year_stripe_sub_key == $subscription->stripe_plan) {
                                $period = $date->addYear();
                            } else {
                                $period = $date->addMonth();
                            }
                            $company->active_until = $period;
                            $company->membership_id = $membership->id;
                            $subscription->ends_at = $period;
                            $company->save();
                            $subscription->save();
                        }
                    } else {
                        $message = 'Missing membership for stripe invoice ' . $params['data']['object']['id'] . ' been paid by undefined customer ' . $params['data']['object']['customer'];
                        $this->notifyAdmin($message);
                    }
                }
            } else {
                $message = 'Missing subscription ' . $params['data']['object']['subscription'] . ' been paid by undefined customer ' . $params['data']['object']['customer'];
                $this->notifyAdmin($message);
            }
        } else {
            $message = 'Invoice  ' . $params['data']['object']['id'] . ' been paid by undefined customer ' . $params['data']['object']['customer'];
            $this->notifyAdmin($message);
        }
        return ['message' => 'success'];
    }

    public function handleChargeRefunded(array $params):array
    {
        Log::channel('stripehooklog')->info($params);
        $company = Company::where('stripe_id', $params['data']['object']['customer'])->first();
        if (!empty($company)) {
            $invoice = Invoice::where('stripe_id', $params['data']['object']['payment_intent'])->first();
            if(!empty($invoice)){
                $invoice->payment_status = Invoice::STATUS_REFUNDED;
                $invoice->save();
                if ($invoice->recurring_id > 0){
                    CompanySubscriptions::manager()->delete($invoice->recurring_id);
                }
                $membership = Membership::where('id', $invoice->membership_id)->first();
                if(!empty($membership)){
                    $expired_date = Carbon::parse($company->active_until);
                    if($invoice->price == $membership->month_price){
                        $expired_date->subMonth();
                    }
                    if($invoice->price == $membership->year_price){
                        $expired_date->subYear();
                    }
                    if ($expired_date->lt(Carbon::now())) {
                        $expired_date = Carbon::now()->addDay();
                    }
                    $company->active_until = $expired_date;
                    $company->save();
                }

            } else {
                $message = 'Charge Refunded for undefined payment  ' . $params['data']['object']['payment_intent'] . ' came for customer ' . $params['data']['object']['customer'];
                $this->notifyAdmin($message);
            }
        } else {
            $message = 'Charge Refunded for payment  ' . $params['data']['object']['payment_intent'] . ' came for undefined customer ' . $params['data']['object']['customer'];
            $this->notifyAdmin($message);
        }
        return ['message' => 'success'];
    }

    public function handleInvoiceFailed(array $params):array
    {
        Log::channel('stripehooklog')->info($params);
        $company = Company::where('stripe_id', $params['data']['object']['customer'])->first();
        if (!empty($company)) {
            $subscription = CompanySubscriptions::where('stripe_id',$params['data']['object']['subscription'])->first();
            if (!empty($subscription)) {
                $invoice = Invoice::where('stripe_id', $subscription->stripe_id . '_initial')->first();
                if (!empty($invoice)) {
                    $invoice->payment_status = Invoice::STATUS_FAILED;
                    $invoice->save();
                } else {
                    $membership = Membership::where('year_stripe_sub_key', $subscription->stripe_plan)->orWhere('month_stripe_sub_key', $subscription->stripe_plan)->first();
                    if (!empty($membership)) {
                        $invoice = Invoice::create([
                            'title' => Invoice::manager()->generateInvoiceID(),
                            'price' => $membership->year_stripe_sub_key == $subscription->stripe_plan ? $membership->year_price : $membership->month_price,
                            'stripe_id' => $params['data']['object']['payment_intent'],
                            'payment_status' => Invoice::STATUS_FAILED,
                            'membership_id' => $membership->id,
                            'company_id' => $company->id,
                        ]);
                    }
                }
                $stripe_subscription = \Stripe\Subscription::retrieve($subscription->stripe_id);
                if(!empty($stripe_subscription)){
                    $stripe_subscription->cancel();
                    $subscription->delete();
                } else {
                    $message = 'Failed to cancel Subscription ' . $params['data']['object']['subscription'] . ' of  customer ' . $params['data']['object']['customer'];
                    $this->notifyAdmin($message);
                }
            } else {
                $message = 'Missing subscription ' . $params['data']['object']['subscription'] . ' been failed by  customer ' . $params['data']['object']['customer'];
                $this->notifyAdmin($message);
            }
        } else {
            $message = 'Invoice  ' . $params['data']['object']['id'] . ' been failed by undefined customer ' . $params['data']['object']['customer'];
            $this->notifyAdmin($message);
        }
        return ['message' => 'success'];
    }

    public function handleCheckoutSessionCompleted(array $params):array
    {
        Log::channel('stripehooklog')->info($params);
        $company = Company::where('stripe_id', $params['data']['object']['customer'])->first();
        if (!empty($company)) {
            $session_id = $params['data']['object']['id'];
            if (is_null($params['data']['object']['payment_intent']) && strlen($params['data']['object']['subscription']) > 0) {
                $subscription = CompanySubscriptions::where('stripe_id', $session_id)->first();
                if (!empty($subscription)) {
                    $subscription->stripe_id = $params['data']['object']['subscription'];
                    $subscription->save();
                    $invoice = Invoice::where('stripe_id', $session_id)->where('recurring_id', $subscription->id)->first();
                    if (!empty($invoice)) {
                        $invoice->stripe_id = $subscription->stripe_id . '_initial';
                        $invoice->save();
                    } else {
                        $message = 'Missing start invoice for subscription ' . $subscription->stripe_id . ' created by customer ' . $company->title;
                        $this->notifyAdmin($message);
                    }
                } else {
                    $message = 'Undefined Subscription ' . $params['data']['object']['subscription'] . ' created by customer ' . $company->title;
                    $this->notifyAdmin($message);
                }
            }
        } else {
            $message = 'Checkout ' . $params['data']['object']['id'] . ' created from undefined customer ' . $params['data']['object']['customer'];
            $this->notifyAdmin($message);
        }
        return ['message' => 'success'];
    }

    public function handlePaymentIntentSucceeded(array $params):array
    {
        Log::channel('stripehooklog')->info($params);
        $company = Company::where('stripe_id', $params['data']['object']['customer'])->first();
        if (!empty($company)) {
            $invoice = Invoice::where('stripe_id', $params['data']['object']['id'])->first();
            if (!empty($invoice)) {
                if ($invoice->payment_status != Invoice::STATUS_SUCCEEDED) {
                    $invoice->payment_status = Invoice::STATUS_SUCCEEDED;
                    $invoice->stripe_url = $params['data']['object']["charges"]['data'][0]['receipt_url'];
                    $invoice->save();
                    $card_data = $params['data']['object']["charges"]['data'][0]['payment_method_details']['card'];
                    if (!empty($card_data)) {
                        $company->card_brand = $card_data['brand'];
                        $company->card_last_four = $card_data['last4'];
                        $company->save();
                    }
                    $membership = Membership::where('id', $invoice->membership_id)->first();
                    if ($company->membership_id == Membership::FREE_PACKAGE_ID || $company->membership_id != $membership->id) {
                        $date = Carbon::now();
                    } else {
                        $date = Carbon::parse($company->active_until);
                    }
                    if ($membership->month_price == $invoice->price) {
                        $date->addMonth();
                    } else {
                        $date->addYear();
                    }
                    $company->active_until = $date;
                    $company->membership_id = $membership->id;
                    $company->save();
                    $old_subscriptions = CompanySubscriptions::where('company_id', $company->id)->get();
                    foreach ($old_subscriptions as $old_subscriptioin) {
                        $sub_membership = Membership::where('year_stripe_sub_key', $old_subscriptioin->stripe_plan)
                            ->orWhere('month_stripe_sub_key', $old_subscriptioin->stripe_plan)->first();
                        if (!empty($sub_membership) && $sub_membership->id < $membership->id) {
                            CompanySubscriptions::manager()->delete($old_subscriptioin->id);
                        }
                    }
                }
            }
        } else {
            $message = 'Payment ' . $params['data']['object']['id'] . ' succeeded from undefined customer ' . $params['data']['object']['customer'];
            $this->notifyAdmin($message);
        }
        return ['message' => 'success'];
    }

    public function handlePaymentIntentPaymentFailed(array $params):array
    {
        Log::channel('stripehooklog')->info($params);
        $company = Company::where('stripe_id', $params['data']['object']['customer'])->first();
        if (!empty($company)) {
            $invoice = Invoice::where('stripe_id', $params['data']['object']['id'])->first();
            if (!empty($invoice)) {
                $invoice->payment_status = Invoice::STATUS_FAILED;
            }
        } else {
            $message = 'Payment ' . $params['data']['object']['id'] . ' failed by undefined customer ' . $params['data']['object']['customer'];
            $this->notifyAdmin($message);
        }
        return ['message' => 'success'];
    }

    public function handlePaymentIntentCanceled(array $params):array
    {
        Log::channel('stripehooklog')->info($params);
        $company = Company::where('stripe_id', $params['data']['object']['customer'])->first();
        if (!empty($company)) {
            $invoice = Invoice::where('stripe_id', $params['data']['object']['id'])->first();
            if (!empty($invoice)) {
                $invoice->payment_status = Invoice::STATUS_CANCELLED;
            }
        } else {
            $message = 'Payment ' . $params['data']['object']['id'] . ' failed by undefined customer ' . $params['data']['object']['customer'];
            $this->notifyAdmin($message);
        }
        return ['message' => 'success'];
    }

    public function handlePaymentIntentProcessing(array $params):array
    {
        Log::channel('stripehooklog')->info($params);
        $company = Company::where('stripe_id', $params['data']['object']['customer'])->first();
        if (!empty($company)) {
            $invoice = Invoice::where('api_id', $params['data']['object']['id'])->first();
            if (!empty($invoice)) {
                $invoice->payment_status = Invoice::STATUS_PENDING;
            }
        } else {
            $message = 'Payment ' . $params['data']['object']['id'] . ' failed by undefined customer ' . $params['data']['object']['customer'];
            $this->notifyAdmin($message);
        }
        return ['message' => 'success'];
    }

    protected function notifyAdmin(string $message):void
    {
        Mail::to(env('ADMINISTRATOR_EMAIL'))->send(new CommonEmail(['message' => $message, 'title' => 'Stripe Account ALERT']));
        Log::channel('stripehooklog')->info($message);
    }




}

<?php
declare(strict_types=1);

namespace App\Domain\Billing;


use App\Foundation\Bridge\Laravel\UpTrait;
use App\Models\CompanySubscriptions;
use Stripe\Stripe;
use Illuminate\Support\Facades\Log;

class SubscriptionManager
{
    use UpTrait;

    public function delete(int $id): bool
    {
        $subscription = CompanySubscriptions::where('id', $id)->first();
        if (!empty($subscription)) {
            Stripe::setApiKey(env('STRIPE_SECRET'));
            try {
                $stripe_subscription = \Stripe\Subscription::retrieve($subscription->stripe_id);
                if (!empty($stripe_subscription)) {
                    $stripe_subscription->cancel();
                    Log::channel('stripehooklog')->info('Subscrption id:' . $subscription->id . ', stripe_id:' . $subscription->stripe_id . ' been deleted.');
                    $subscription->delete();
                } else {
                    $subscription->delete();
                    Log::channel('stripehooklog')->info('Subscrption id:' . $subscription->id . ', stripe_id:' . $subscription->stripe_id . ' been deleted but did not found on stripe.');
                }
            } catch (\Exception $e) {
                $subscription->delete();
                Log::channel('stripehooklog')->info('Subscrption id:' . $subscription->id . ', stripe_id:' . $subscription->stripe_id . ' been deleted but did not found on stripe.');
            }
            return true;
        }
        return false;
    }

    public function deleteByStripeId(string $stripe_id): bool
    {
        $subscription = CompanySubscriptions::where('stripe_id', $stripe_id)->first();
        if (!empty($subscription)) {
            return ($this->delete($subscription->id));
        }
        return false;
    }


}
<?php

namespace App\Models;

use App\Domain\Billing\SubscriptionManager;
use Illuminate\Database\Eloquent\Model;

class CompanySubscriptions extends Model
{
    protected $table = 'company_subscriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'company_id',
        'name',
        'stripe_id',
        'stripe_plan',
        'quantity',
        'trial_ends_at',
        'ends_at',
    ];

    public static function manager(): SubscriptionManager
    {
        return SubscriptionManager::up();
    }
}

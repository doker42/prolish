<?php

namespace App\Models;

use App\Domain\Billing\InvoiceManager;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'title',
        'price',
        'stripe_id',
        'payment_status',
        'membership_id',
        'company_id',
    ];

    const STATUS_CREATED = 'custom.payment_open';
    const STATUS_SUCCEEDED = 'custom.payment_paid';
    const STATUS_FAILED = 'custom.payment_failed';
    const STATUS_CANCELLED = 'custom.payment_canceled';
    const STATUS_PENDING = 'custom.payment_pending';
    const STATUS_REFUNDED = 'custom.payment_refunded';

    protected $hidden = [
        'recurring_id'
    ];

    public function getPaidAttribute() {
        if ($this->payment_status == 'custom.payment_paid') {
            return true;
        }
        return false;
    }

    public function membership()
    {
        return $this->hasOne('App\Models\Membership', 'id', 'membership_id');
    }

    public function company()
    {
        return $this->hasOne('App\Models\Company', 'id', 'company_id');
    }

    public static function manager(): InvoiceManager
    {
        return InvoiceManager::up();
    }
}

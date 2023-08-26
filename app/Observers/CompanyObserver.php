<?php

namespace App\Observers;

use App\Models\Company;
use App\Models\Membership;

class CompanyObserver
{
    /**
     * Handle the company "creating" event.
     *
     * @param  \App\Models\Company  $company
     * @return void
     */
    public function creating(Company $company)
    {
        if (!$company->membership_id) {
            $membership = Membership::where('id', Membership::FREE_PACKAGE_ID)->first();

            if ($membership) {
                $company->membership_id = $membership->id;
            }
        }
    }

}

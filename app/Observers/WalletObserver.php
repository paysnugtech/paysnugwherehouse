<?php

namespace App\Observers;

use App\Models\Department;
use App\Models\Wallet;

class WalletObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function created(Department $department)
    {
        // Create a wallet for the new user
        Wallet::create([
            'dept_id' => $department->id,
            'currency' => 'NGN',
            'currency_code' => 'NGN',
            'country' => 'Nigeria',
            'country_id' => 'c1d72499-5ae6-481a-a52d-247109e1724e'

        ]);
    }

    // Other observer methods can be added here for additional events
}
 
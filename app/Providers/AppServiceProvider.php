<?php

namespace App\Providers;

use App\Models\Company;
use App\Models\ItemUrl;
use App\Models\NotificationsUser;
use App\Models\User;
use App\Observers\NotificationsUserObserver;
use Laravel\Cashier\Cashier;
use App\Observers\CompanyObserver;
use App\Observers\ItemUrlObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Validator::extend('image64', function ($attribute, $value, $parameters, $validator) {
            $type = explode('/', explode(':', substr($value, 0, strpos($value, ';')))[1])[1];
            if (in_array($type, $parameters)) {
                return true;
            }
            return false;
        });

        Validator::replacer('image64', function($message, $attribute, $rule, $parameters) {
            return str_replace(':values',join(",",$parameters),$message);
        });

        Company::observe(CompanyObserver::class);
        ItemUrl::observe(ItemUrlObserver::class);
        User::observe(UserObserver::class);
        NotificationsUser::observe(NotificationsUserObserver::class);
        Cashier::useCurrency('eur', '€');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Payment;
use App\Observers\PaymentObserver;
use App\Models\Lease;
use App\Observers\LeaseObserver;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Payment::observe(PaymentObserver::class);
        Lease::observe(LeaseObserver::class);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('super_admin') ? true : null;
        });
    }
}

<?php

namespace App\Providers;

use App\Models\Coupon;
use App\Models\EventAssistant;
use App\Observers\CouponObserver;
use App\Observers\EventAssistantObserver;
use Illuminate\Support\ServiceProvider;

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
        EventAssistant::observe(EventAssistantObserver::class);
        Coupon::observe(CouponObserver::class);
    }
}

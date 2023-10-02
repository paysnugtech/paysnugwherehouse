<?php

namespace App\Providers;
use App\Models\Department;
use App\Observers\WalletObserver;
use App\ResponseHandler;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('response-handler', function ($app) {
            return new ResponseHandler();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Department::observe(WalletObserver::class);
    }
}

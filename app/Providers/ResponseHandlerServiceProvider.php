<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ResponseHandler;

class ResponseHandlerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(ResponseHandler::class, function ($app) {
            return new ResponseHandler();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}

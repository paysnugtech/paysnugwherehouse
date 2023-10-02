<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;
use App\Interfaces\BankService;
use App\Services\Bank\Vfd;
use App\Services\Bank\SafeHaven;
use App\Services\Bank\Paysnug;

class BankServiceProvider extends ServiceProvider {
    public function register() {
        $this->app->bind(BankService::class, function ($app, $parameters) {
            $selectedApi = $parameters['selectedApi'] ?? 'VFD'; // Access selectedApi from parameters
            switch ($selectedApi) {
                case 'VFD':
                    return new Vfd();
                case 'SAFEHAVEN':
                    return new SafeHaven();
                    case 'PAYSNUG':
                        return new Paysnug();
                // Add more cases for other APIs
            }
        });
    }
}


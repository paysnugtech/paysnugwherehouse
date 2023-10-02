<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Jobs\CheckTransactionStatus;
use App\Jobs\CheckReversalStatus;
use App\Interfaces\BankService;



class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $BankService = app(BankService::class);
    $schedule->job(new CheckTransactionStatus($BankService))->everyFiveMinutes();
    $schedule->job(new CheckReversalStatus($BankService))->everyFiveMinutes();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

}

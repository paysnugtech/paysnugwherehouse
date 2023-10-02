<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use App\Models\Hooks;

class InstantNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle()
    {
        $data = $this->data;
        $this->transferNotice($data);
    }

    public function transferNotice($data)
    {
        $hooksUrl = Hooks::where('wallet_id', $data["data"]["walletId"])->first();
        if(!$hooksUrl){
        return;
        }
        \Log::info('started connection');
        Http::withHeaders([
            'Authorization' => 'Bearer ',
        ])->post($hooksUrl->notification, $data);
        \Log::info($data);
        \Log::info($hooksUrl->notification);
    }
}

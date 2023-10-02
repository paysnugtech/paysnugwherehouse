<?php
namespace App\Jobs;

use App\Interfaces\BankService;
use App\Models\Transaction;
use App\Models\BankTransaction;
use App\Models\ReversalTransaction;
use App\Models\Hooks;
use App\Models\Wallet;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckReversalStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(BankService $BankService)
    {
        $jsonFilePath = storage_path('app/bank_instruction.json');
        $instructionsJson = json_decode(Storage::disk('local')->get('bank_instruction.json'), true);
        $pendingTransactions = ReversalTransaction::where('status', 2)
            ->get();
        if(!$pendingTransactions){
            return;
        }
        foreach ($pendingTransactions as $transaction) {
            $transactionId =$transaction->session_id;
            if($transaction->provider_name == "VFD"){
                $transactionId =$transaction->reference;
            }
            
            $BankService = app()->makeWith(BankService::class, ['selectedApi' => $transaction->provider_name]);
            $transactionStatus = $BankService->reversalStatus($transactionId);
            Log::info($transactionStatus);

            $transactionUpdate = Transaction::where('reference', $transaction->reference)->first();
            $banktransaction = BankTransaction::where('reference', $transaction->reference)->first();
            if($transactionStatus["status"]=="00"||$transactionUpdate->response_code=="999"||$transactionUpdate->response_code=="51"||$transactionStatus["status"]=="25"){
                
                $provider_ref = $transaction->provider_ref;
                $reference = $transaction->reference;
                $transactionUpdate->update(['status' => 0, 'is_reversed' =>1]);
                $banktransaction->update(['status' => 0, 'is_reversed' =>1]);
                $transaction->update(['status' => 1, 'is_reversed' =>1]);
                $wallet = Wallet::where('id', $transaction->wallet_id)
                ->first();
                $total_amount = $transaction->amount + $transaction->fee;
                $incrementSuccess = $wallet->increment('balance', $total_amount);
        
                    $hooksUrl = Hooks::where('wallet_id', $transaction->wallet_id)->first();
                    if(!$hooksUrl){
                        return;
                    }
                    $notificationData = [
                        'message'=>"Reversal successfull",
                        'data'=>[
                            'status'=>"00",
                            'amount'=>$transaction->amount,
                            'fee'=>$transaction->fee,
                            'reference'=> $transaction->reference,
                            'reversalId'=> $transaction->id,
                        ],
                    ];
                    Http::withHeaders([
                        'Authorization' => 'Bearer ',
                    ])->post($hooksUrl->reversal, $notificationData);
            }
            
        }
    
    }
    
}

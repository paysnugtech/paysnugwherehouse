<?php
namespace App\Jobs;

use App\Interfaces\BankService;
use App\Models\Transaction;
use App\Models\BankTransaction;
use App\Models\ReversalTransaction;
use App\Models\Hooks;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CheckTransactionStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    public function handle(BankService $BankService)
    {
    
        $jsonFilePath = storage_path('app/bank_instruction.json');
        $instructionsJson = json_decode(Storage::disk('local')->get('bank_instruction.json'), true);
    
        $thresholdTime = now()->subHours(24);
    
        // Retrieve pending transactions not older than 24 hours
        $pendingTransactions = Transaction::where('status', 2)
            ->where('created_at', '>=', $thresholdTime)
            ->get();
    
        foreach ($pendingTransactions as $transaction) {
            $transactionId =$transaction->session_id;
            if($transaction->provider_name == "VFD"){
                $transactionId =$transaction->reference;
            }
            $BankService = app()->makeWith(BankService::class, ['selectedApi' => $transaction->provider_name]);
            $transactionStatus = $BankService->checkStatus($transactionId);
            
                $transaction = Transaction::where('reference', $transaction->reference)->first();
                $banktransaction = BankTransaction::where('reference', $transaction->reference)->first();
                
                $sessionId = array_key_exists("data", $transactionStatus) ? $transactionStatus["data"]["sessionId"] : "failed";
                $provider_ref = $transaction->provider_ref;
                $reference = $transaction->reference;
                $response_code = $transactionStatus["data"]["transactionStatus"];
                $instruction = $instructionsJson[$response_code];
                $statusreport = $instruction["status"];
                
                // Map statusreport values to the desired status values
                $statusMap = [
                    "SUCCESS" => 1,
                    "PENDING" => 2,
                    "FAILED" => 0,
                ];
        
                // Determine the status based on the mapping or set it to 'unknown' if not found
                $status = $statusMap[$statusreport] ?? '999';
        
                $transaction->updateTransactionData($sessionId, $provider_ref, $response_code,$transaction->provider_response, $status, $transaction->provider_name);
                $banktransaction->updateTransactionData($sessionId, $provider_ref, $response_code,$banktransaction->provider_response, $response_code);


        
            
                if ($instruction["reversal"] === '3') {

                    $reversalTransaction = new ReversalTransaction([
                        'dept_id' => $transaction->dept_id,
                        'wallet_id' => $transaction->wallet_id,
                        'trx_id' => $transaction->id,
                        'reference' => $transaction->reference,
                        'provider_ref' => $transaction->provider_ref,
                        'currency' => $transaction->currency,
                        'provider_name' => $transaction->provider_name,
                        'amount' => $transaction->amount,
                        'fee' => $transaction->fee,
                        'channel' => $transaction->channel,
                    ]);
                    $reversalTransaction->save();
                    }

                    $hooksUrl = Hooks::where('wallet_id', $transaction->wallet_id)->first();
                    if(!$hooksUrl){
                        return;
                    }

                    $responseCode = ($response_code == 51) ? 999 : $response_code;
                    $message = ($response_code == 51) ? "Transaction Failed" : $instruction["description"];

                    $notificationData = [
                        'message'=>$message,
                        'data'=>[
                            'status'=>$responseCode,
                            'amount' => $transaction->amount,
                            'fee' => $transaction->fee,
                            'reference'=> $transaction->reference,
                            'trxId'=> $transaction->id,
                        ],
                    ];
                    Http::withHeaders([
                        'Authorization' => 'Bearer ',
                    ])->post($hooksUrl->notification, $notificationData);

                
            
        }
  
    }
    

  
}

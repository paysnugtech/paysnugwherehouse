<?php

namespace App\Services\Bank;
use App\Jobs\InstantNotification;
use App\Interfaces\BankService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Transaction;
use App\Models\BankTransaction;
use App\Models\Department;
use App\Models\AccountDetail;
use App\Models\Wallet;
use App\Services\ResponseHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;



class Paysnug implements BankService
{
    public function psgInit($data)
    {
        $account = AccountDetail::where('account_number', $data["accountNo"])
        ->first();

        Log::info("the paysnug process");
        Log::info($account);
    

        return $account;
    }

    public function initiateTransfer($data)
    {
        $jsonFilePath = storage_path('app/bank_instruction.json');
        $instructionsJson = json_decode(Storage::disk('local')->get('bank_instruction.json'), true);
        $validateAccount = $this->psgInit($data);
        
        if ($validateAccount) {
            Log::info("the paysnug process2");
            Log::info($validateAccount);
    
            $wallet = Wallet::where('id', $validateAccount->wallet_id)
                ->first();
                $transaction = Transaction::where('reference', $data["reference"])->first();
                $banktransaction = BankTransaction::where('reference', $data["reference"])->first();
    
            try {
                $wallet->increment('balance', $data["amount"]);
                $randomNumber = mt_rand(100000, 999999); // Generate a random 6-digit number
                $currentDateTime = date('dmYHis'); // Get the current date and time in the format ddmmyyhhmmss
                $sessionId = $randomNumber . $currentDateTime. $data["accountNo"]; // Concatenate the random number and current date/time
                $status = 1;
                $wherehouse_response = 00;
                $response_code = 00;
                $provider_ref = $data['reference'];

                $transaction->updateTransactionData($sessionId, $provider_ref, $response_code,$wherehouse_response, $status, 'PAYSNUG');
                $banktransaction->updateTransactionData($sessionId, $provider_ref, $response_code, $wherehouse_response, $status);

                $transaction = new Transaction([
                    'dept_id' => $wallet->dept_id,
                    'wallet_id' => $wallet->id,
                    'reference' => $data['reference']."_".rand(),
                    'provider_ref' => $data['reference'],
                    'currency' => $wallet->currency_code,
                    'transaction_type' => 'In-NIBSS',
                    'provider_name' => "PAYSNUG",
                    'channel' => 'Wharehouse',
                    'session_ID' => 'Wharehouse',
                    'before_balance' => $wallet->balance,
                    'after_balance' =>  $wallet->balance + $data["amount"],
                    'amount' => $data["amount"],
                    'provider_response' => 00,
                    'response_code' => 00,
                    'fee' => 0,
                    'status' => 1, // Set to pending initially
                ]);
    
                $transaction->save();
    
                $responded = [
                    'status' => "00",
                    'message' => "Approved or completed successfully",
                    'data'=>[
                    'sessionId' => $sessionId,
                    'walletId' => $transaction->wallet_id,
                    ],
                ];
    
                $responseHandler = app(ResponseHandler::class);
                $formattedResponse = $responseHandler->bankTransfer($responded);
                
                // Create an instance of the InstantNotification job
                $instantNotificationJob = new InstantNotification($responded);
            
                // Call the transferNotice method on the job instance
                $instantNotificationJob->transferNotice($responded);
            
                return $formattedResponse;
            } catch (\Exception $e) {
                \Log::error($e);
                return response()->json(['error' => 'Transaction failed, Check status before requery'], 500);
            }
        }
    
        return [
            "responseCode" => "Account Not Available",
            "message" => "Check Transaction status before requery"
        ];
    }
    

    public function checkStatus($transactionId)
    {
        $url = env("VFDURL") . 'transactions';
        $queryParams = http_build_query([
            'reference' => $transactionId,
            'wallet-credentials' => env("VFD_WALLET_CREDENTIALS1"),
        ]);

        $url .= '?' . $queryParams;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env("VFD_TOKEN1"),
        ])->get($url);
        $data = $response->json();
        $sessionId = isset($data["data"]["sessionId"]) ? $data["data"]["sessionId"] : "failed";
        $amount = isset($data["data"]["amount"]) ? $data["data"]["amount"] : "nill";
        $transactionStatus = isset($data["data"]["transactionStatus"]) ? $data["data"]["transactionStatus"] : "999";
        Log::info("the info2");
        Log::info($data);
        $return = [
            'status'=>$data["status"],
            'data' =>[
                'sessionId'=> $sessionId,
                'transactionStatus' => $transactionStatus,
                'amount' => $amount,
            ],

            ];

        return $return;
    }

    public function reversalStatus($transactionId)
    {
        $url = env("VFDURL") . 'transactions/reversal';
        $queryParams = http_build_query([
            'reference' => $transactionId,
            'wallet-credentials' => env("VFD_WALLET_CREDENTIALS1"),
        ]);

        $url .= '?' . $queryParams;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env("VFD_TOKEN1"),
        ])->get($url);

        return $response->json();
    }


    public function createAccountIndividual($data){
        $dept_id = $data["dept_id"];
        $dept = Department::where('id', $dept_id)->first();
        if ($dept->dept_type === "App") {
            $phone_number = $data["phone_number"];
            $is_app = "App"; 
            $account_number = preg_replace('/^(234|0)/', '', $phone_number);         
        } 
        else {
            $nextNonPhoneSequenceNumber = 1;
            $lastNonPhoneAccount = AccountDetail::where('dept_type', 'Others')
                ->orderBy('account_number', 'desc')
                ->first();
            if ($lastNonPhoneAccount) {
                $nextNonPhoneSequenceNumber = $lastNonPhoneAccount->account_number + 1;
            }
            $account_number = str_pad($nextNonPhoneSequenceNumber, 10, '0', STR_PAD_LEFT);
            $is_app = $dept->dept_type === "App"?$dept->dept_type:"Others";
        }

        return [
            "account_number"=>$account_number,
            "account_name" => $data["account_holder_name"],
            "is_app"=>$is_app,
            "provider_name"=>"PAYSNUG",
            "provider_code"=>"9999A"

        ];


    }

    public function bankList(){

        
    }
    public function nameEnquiry($data){
        $response = $this->psgInit($data);
        if ($response) {
        return [
            "status"=>"00",
            "message"=>"Completed Successfully",
            "account_number" => $data["accountNo"],
            "account_name" => $response->account_holder_name
        ];
        }
        return[
            "status"=>108,
            "message"=>"Failed"
        ];
    }

    
    
}

<?php

namespace App\Services\Bank;
use App\Jobs\InstantNotification;
use App\Interfaces\BankService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\Transaction;
use App\Models\BankTransaction;
use App\Models\Department;
use App\Services\ResponseHandler;
use Illuminate\Support\Facades\Log;



class Vfd implements BankService
{
    public function vfdInit($data)
    {
        $transfer_type = $data["bankCode"] == "090110" ? "intra" : "inter";
        $url = env("VFDURL") . 'transfer/recipient';
        $queryParams = http_build_query([
            'transfer_type' => 'inter',
            'accountNo' => $data["accountNo"],
            'bank' => $data["bankCode"],
            'wallet-credentials' => env("VFD_WALLET_CREDENTIALS1"),
        ]);

        $url .= '?' . $queryParams;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env("VFD_TOKEN1"),
        ])->get($url);

        return $response->json();
    }

    public function initiateTransfer($data)
    {
        $jsonFilePath = storage_path('app/bank_instruction.json');
        $instructionsJson = json_decode(Storage::disk('local')->get('bank_instruction.json'), true);
        $validateAccount = $this->vfdInit($data);
        $transfer_type = $data["bankCode"] == "090110" ? "intra" : "inter";
        $remark = array_key_exists("naration", $data) ? $data["naration"] : "transfer from paysnug";
        $url = env("VFDURL") . 'transfer';
        $hashed = env("VFDACCOUNT1") . '' . $data["accountNo"];
        $signature = hash("sha512", $hashed);
        $queryParams = http_build_query([
            'wallet-credentials' => env("VFD_WALLET_CREDENTIALS1"),
        ]);

        $url .= '?' . $queryParams;

        if ($validateAccount) {
            $trxData = [
                "fromSavingsId" => env("VFDSAVINGID1"),
                "amount" => $data["amount"],
                "toAccount" => $data["accountNo"],
                "fromBvn" => "22244814409",
                "signature" => $signature,
                "fromAccount" => env("VFDACCOUNT1"),
                "toBvn" => $validateAccount["data"]["bvn"],
                "remark" => $remark,
                "fromClientId" => env("VFDCLIENTID1"),
                "fromClient" => "Admin Wallet",
                "toKyc" => $validateAccount["data"]["status"],
                "toSavingsId" => $validateAccount["data"]["account"]["id"],
                "reference" => $data["reference"],
                "toClientId" => $validateAccount["data"]["clientId"],
                "toClient" => $remark,
                "toSession" => $validateAccount["data"]["account"]["id"],
                "transferType" => $transfer_type,
                "toBank" => $data["bankCode"]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env("VFD_TOKEN1"),
            ])->post($url, $trxData);

            $transaction = Transaction::where('reference', $data["reference"])->first();
            $banktransaction = BankTransaction::where('reference', $data["reference"])->first();
            $responsedata = $response->json();
            $sessionId = isset($responsedata["data"]["sessionId"]) ? $responsedata["data"]["sessionId"] : "failed";
            $provider_ref = isset($responsedata["data"]["reference"]) ? $responsedata["data"]["reference"] : "nill";
            $response_code = $responsedata["status"];
            $instruction = $instructionsJson[$response_code];
            $statusreport = $instruction["status"];
            $statusMap = [
                "SUCCESS" => 1,
                "PENDING" => 2,
                "FAILED" => 2,
            ];
            $status = $statusMap[$statusreport] ?? 2;
            $wherehouse_response = ($response_code == 51) ? 999 : $response_code;

            $transaction->updateTransactionData($sessionId, $provider_ref, $response_code,$wherehouse_response, $status, 'VFD');
            $banktransaction->updateTransactionData($sessionId, $provider_ref, $response_code, $wherehouse_response, $status);

            if ($response->successful()) {
                $responseCode = ($response_code == 51) ? 999 : $response_code;
                    $message = ($response_code == 51) ? "Transaction Failed" : $instruction["description"];
                $responded = [
                    'status' => $responseCode,
                    'message' => $message,
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
            }

            

            return [
                "status" => "990",
                "message" => "Check Transaction status before requery"
            ];
        }
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
        $url = env("VFDURL") . 'client/individual';
        $data = [
            "firstname" => "-",
            "lastname" =>$data['account_holder_name'],
            "middlename" => "",
            "dob" => "04 October 1990",
            "address" => "No 6 Road",
            "gender" => "Unspecify",
            "phone" => $data["phone_number"],
            "bvn" => rand(),
        ];
        $queryParams = http_build_query([
            'wallet-credentials' => env("VFD_WALLET_CREDENTIALS1"),
        ]);

        $url .= '?' . $queryParams;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env("VFD_TOKEN1"),
        ])->post($url, $data);
        if ($response->successful()) {
            return[
                "account_number"=>$data["data"]["accountNo"],
                "is_app"=>$is_app,
                "provider_name"=>"VFD MFB",
                "provider_code"=>"090110"
            ];
        }
        else{
            Log::info("the info");
            Log::info($response->json());
            return false;
        }

        return $response->json();

    }

    public function bankList(){

        $url = env("VFDURL") . 'bank';
        $queryParams = http_build_query([
            'wallet-credentials' => env("VFD_WALLET_CREDENTIALS1"),
        ]);

        $url .= '?' . $queryParams;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env("VFD_TOKEN1"),
        ])->get($url);
        $responseArray = json_decode($response, true);

        if ($response->successful()) {
            $transformedBanks = [];
            $newBankData = [
                [
                    "bank_code" => "9999A",
                    "bank_name" => "PAYSNUG AFRICA"
                ]
            ];
        
            foreach ($response["data"]["bank"] as $responsebank) {
                $transformedBanks[] = [
                    "bank_code" => $responsebank["code"],
                    "bank_name" => $responsebank["name"]
                ];
            }

            $mergedBanks = array_merge($newBankData, $transformedBanks);
        
            return [
                "status"=> $response["status"],
                "message" => "Successfully Fetch",
                "banks" => $mergedBanks
            ];
        }

        return [
            "status"=> 108,
            "message" => "Failed Fetch",
            "banks" => false
        ];

    }

    public function nameEnquiry($data){
        $response = $this->vfdInit($data);
        if ($response) {
        return [
            "status"=>"00",
            "message"=>"Completed Successfully",
            "account_number" => $data["accountNo"],
            "account_name" => $response["data"]["name"]
        ];
        }
        return[
            "status"=>108,
            "message"=>"Failed"
        ];
    }
    
}

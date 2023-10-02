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



class SafeHaven implements BankService
{
    public function tokenInt(){
        $url = env("SAFAVENURL") . 'oauth2/token';
        $data = [
            "client_assertion_type" => "urn:ietf:params:oauth:client-assertion-type:jwt-bearer",
            "grant_type" => "client_credentials",
            "client_assertion" => env("SAFAVENCLINETASSERTION1"), // Use 'client_assertion' key
            "client_id" => env("SAFAVENCLIENTID1"),
        ];
        
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])
        ->post($url, $data);
        
        return $response->json();
        
    }
    public function safehavenInit($data)
    {
        $token = $this->tokenInt();
        $data = [
            'bankCode' => $data["bankCode"],
            'accountNumber' => $data["accountNo"],
        ];
        $url = env("SAFAVENURL") . 'transfers/name-enquiry';
        $response = Http::withHeaders([
            'ClientID' => env("SAFAVENIBS1"),
            'Authorization' => 'Bearer ' . $token["access_token"],
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])
        ->post($url, $data);
        return $response->json();
    }

    public function initiateTransfer($data)
    {   $bankInfo = $this->safehavenInit($data);
        $token = $this->tokenInt();
        $jsonFilePath = storage_path('app/bank_instruction.json');
        $instructionsJson = json_decode(Storage::disk('local')->get('bank_instruction.json'), true);
        $remark = array_key_exists("naration", $data) ? $data["naration"] : "transfer from paysnug";
        $url = env("SAFAVENURL") . 'transfers';

        if ($bankInfo) {
            $trxData = [
                "saveBeneficiary" => false,
                "nameEnquiryReference" => $bankInfo["data"]["sessionId"],
                "debitAccountNumber" => "0113724347",
                "beneficiaryBankCode" => $bankInfo["data"]["bankCode"],
                "beneficiaryAccountNumber" => $data["accountNo"],
                "narration" => "Test Transfer",
                "amount" => floatval($data["amount"]),
                "paymentReference" => $data["reference"],
            ];

            $response = Http::withHeaders([
                'timeout' => 120,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'ClientID' => env("SAFAVENIBS1"),
                'Authorization' => 'Bearer ' . $token["access_token"],
            ])
            ->post($url, $trxData);
            

            $transaction = Transaction::where('reference', $data["reference"])->first();
            $banktransaction = BankTransaction::where('reference', $data["reference"])->first();
            $responsedata = $response->json();
            $sessionId = isset($responsedata["data"]["sessionId"]) ? $responsedata["data"]["sessionId"] : "failed";
            $provider_ref = isset($responsedata["data"]["paymentReference"]) ? $responsedata["data"]["paymentReference"] : "nill";
            $response_code = $responsedata["responseCode"];
            $instruction = $instructionsJson[$response_code];
            $statusreport = $instruction["status"];
            $statusMap = [
                "SUCCESS" => 1,
                "PENDING" => 2,
                "FAILED" => 2,
            ];
            $status = $statusMap[$statusreport] ?? 2;

            $wherehouse_response = ($response_code == 51) ? 999 : $response_code;

            $transaction->updateTransactionData($sessionId, $provider_ref, $response_code,$wherehouse_response, $status, 'SAFEHAVEN');
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
                "responseCode" => "990",
                "message" => "Check Transaction status before requery"
            ];
        }
    }

    public function checkStatus($transactionId)
    {
        $token = $this->tokenInt();
        $data = [
            'sessionId' => $transactionId
        ];
        $url = env("SAFAVENURL") . 'transfers/status';
        $response = Http::withHeaders([
            'ClientID' => env("SAFAVENIBS1"),
            'Authorization' => 'Bearer ' . $token["access_token"],
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])
        ->post($url, $data);
        Log::info($response->json());
        $data = $response->json();
        $sessionId = isset($data["data"]["sessionId"]) ? $data["data"]["sessionId"] : "failed";
        $amount = isset($data["data"]["amount"]) ? $data["data"]["amount"] : "nill";
        $isReversed = isset($data["data"]["isReversed"]) ? $data["data"]["isReversed"] : false;
        $transactionStatus = isset($data["data"]["transactionStatus"]) ? $data["data"]["transactionStatus"] : "999";
     

        $return = [
            'status'=>$data["responseCode"],
            'data' =>[
                'transactionStatus' => $transactionStatus,
                'sessionId'=> $sessionId,
                'reversalStatus' => $isReversed,
                'amount' => $amount,
            ],

        ];

        return $return;
    }

    public function reversalStatus($transactionId)
    {

        $data = $this->checkStatus($transactionId);
       
        return $data;
    }

    public function createAccountIndividual($data){
        $token = $this->tokenInt();

        $tosend = [
            'validFor' => 900,
            'settlementAccount' => array(
                'bankCode' => '090286',
                'accountNumber' => '0113724347'
            ),
            'amountControl' => 'Fixed',
            'amount' => $data['amount'],
            'accountName' => $data['account_holder_name'],
            'callbackUrl' => 'https://paysnug.link',
        ];
        $url = env("SAFAVENURL") . 'virtual-accounts';
        $response = Http::withHeaders([
            'timeout' => 120,
            'ClientID' => env("SAFAVENIBS1"),
            'Authorization' => 'Bearer ' . $token["access_token"],
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])
        ->post($url, $tosend);

        if ($response->successful()) {
            Log::info($response->json());
            return[
                "account_number"=>$response["data"]["accountNumber"],
                "is_app"=>false,
                "account_name"=>$response["data"]["accountName"],
                "provider_name"=>"SAFEHAVEN",
                "provider_code"=>"090286"
            ];
        }
        else{
            return false;
        }
        return $response->json();

    }
    public function bankList(){
        $token = $this->tokenInt();
        $url = env("SAFAVENURL") . 'transfers/banks';
        
        $response = Http::withHeaders([
            'timeout' => 120,
            'ClientID' => env("SAFAVENIBS1"),
            'Authorization' => 'Bearer ' . $token["access_token"],
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ])
        ->get($url);
        
        $responseArray = json_decode($response, true);

        if ($response->successful()) {
            $transformedBanks = [];
            $newBankData = [
                [
                    "bank_code" => "9999A",
                    "bank_name" => "PAYSNUG AFRICA"
                ]
            ];
        
            foreach ($response["data"] as $responsebank) {
                $transformedBanks[] = [
                    "bank_code" => $responsebank["bankCode"],
                    "bank_name" => $responsebank["name"]
                ];
            }

            $mergedBanks = array_merge($newBankData, $transformedBanks);
        
            return [
                "status"=> "00",
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
        $response = $this->safehavenInit($data);
        if ($response) {
        return [
            "status"=>"00",
            "message"=>"Completed Successfully",
            "account_number" => $response["data"]["accountNumber"],
            "account_name" => $response["data"]["accountName"]
        ];
        }
        return[
            "status"=>108,
            "message"=>"Failed"
        ];


      

    }
    
    
}

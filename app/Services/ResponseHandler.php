<?php

namespace App\Services;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ResponseHandler
{
    public function bankTransfer($responsedata)
    {
        // Get the path to the instruction JSON file
        $jsonFilePath = storage_path('app/bank_instruction.json');
    
        // Check if the JSON file exists
        if (file_exists($jsonFilePath)) {
            $instructionsJson = json_decode(Storage::disk('local')->get('bank_instruction.json'), true);
    
            // Check for JSON decoding errors
            if (json_last_error() === JSON_ERROR_NONE) {
                if (array_key_exists($responsedata["status"], $instructionsJson)) {
                    // If the response code is found in the instructions JSON
                    $instruction = $instructionsJson[$responsedata["status"]];
                    $status = ($responsedata["status"] == 51) ? 999 : $responsedata["status"];
                    $message = ($responsedata["status"] == 51) ? "Transaction Failed" : $instruction['description'];
                    return [
                        'status' => $status,
                        'message' => $message,
                        'sessionId' => $responsedata["data"]["sessionId"],
                    ];
                }
            } else {
                // Handle JSON decoding error
                return [
                    'status' => "999",
                    'message' => 'Check transaction before reprocess',
                    'sessionId' => $responsedata["sessionId"],
                ];
            }
        }

        return [
            'status' => "999",
            'message' => "Transaction Processed",
            'sessionId' => $responsedata["sessionId"],
        ];
    }

    public function accountCreation($responsedata){

        return [
            'status' => "00",
            'message' => 'Account Creation Successful',
            'data'=>[
                'account_number'=> $responsedata->account_number,
                'account_holder_name'=> $responsedata->account_holder_name,
                'provider_name'=> $responsedata->provider_name,
                'account_type'=> $responsedata->account_type,
                
            ],
            //'sessionId' => $responsedata["sessionId"],
        ];

    }
    

}

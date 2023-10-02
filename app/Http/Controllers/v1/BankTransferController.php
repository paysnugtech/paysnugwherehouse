<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Interfaces\BankService;
use App\Models\Wallet;
use App\Models\Transaction;
use App\Models\BankTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator; // Import Validator

class BankTransferController extends Controller
{
    public function transfer(Request $request, BankService $BankService)
    {
        $validatedData = $request->validate([
            'accountNo' => 'required|string|max:255',
            'bankCode' => 'required|string',
            'reference' => 'required|string|max:20',
            'naration' => 'string|max:255',
            'amount' => 'required|numeric',
            'currency' => 'required|string',
            'walletId' => 'required|string',
        ]);

        $data = $request->all();
        $dept_id = $request->attributes->get('dept_id');
        $selectedApi = 'VFD';
        if($data["bankCode"] =="9999A"){
            $selectedApi ="PAYSNUG";
        }
        $BankService = app()->makeWith(BankService::class, ['selectedApi' => $selectedApi]);
        $wallet = Wallet::where('dept_id', $dept_id)
            ->where('id', $data["walletId"])
            ->where('status', 1)
            ->first();

        if (!$wallet) {
            return response()->json(['error' => 'Wallet Not Found'], 422);
        }

        $existing = Transaction::where('reference', $data["reference"])
        ->first();

        if ($existing) {
            return response()->json(['status'=>94,'message' => 'Duplicate Transaction'], 422);
        }

        $amount = $data["amount"];
        $fee = 10;
        $total_amount = $amount + $fee;
        $after_balance = $wallet->balance - $total_amount;

        if ($wallet->balance < $total_amount) {
            return response()->json(['error' => 'Insufficient balance'], 422);
        }

        DB::beginTransaction();

        try {
            // Create the transaction record
            $transaction = new Transaction([
                'dept_id' => $dept_id,
                'wallet_id' => $wallet->id,
                'account_no' => $data['accountNo'],
                'bank_code' => $data['bankCode'],
                'reference' => $data['reference'],
                'provider_ref' => $data['reference'],
                'currency' => $data['currency'],
                'transaction_type' => 'Bank Transfer',
                'provider_name' => $selectedApi,
                'channel' => 'Wharehouse',
                'before_balance' => $wallet->balance,
                'after_balance' => $after_balance,
                'amount' => $amount,
                'fee' => $fee,
                'status' => 2, // Set to pending initially
            ]);
            $transaction->save();

            $bankTransaction = new BankTransaction([
                'dept_id' => $dept_id,
                'wallet_id' => $wallet->id,
                'trx_id' => $transaction->id,
                'session_id' => "NULL",
                'amount' => $amount,
                'account_no' => $data['accountNo'],
                'bank_name' => $data['bankCode'],
                'bank_code' => $data['bankCode'],
                'reference' => $data['reference'],
                'provider_ref' => $data['reference'],
                'currency' =>  $wallet->currency_code,
                'transaction_type' => 'Bank Transfer',
                'provider_name' => $selectedApi,
                'channel' => 'Wharehouse',
                'before_balance' => $wallet->balance,
                'status' => 2, // Set to pending initially
            ]);
            $bankTransaction->save();

            $decrementSuccess = $wallet->decrement('balance', $total_amount);
            
            if (!$decrementSuccess) {
                DB::rollBack();
                return response()->json(['error' => 'Balance decrement failed'], 500);
            }

            // Initiate the bank transfer
            $initTransfer = $BankService->initiateTransfer($data);

            DB::commit();

            return response()->json($initTransfer);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error($e);
            return response()->json(['error' => 'Transaction failed, Check status before requery'], 500);
        }
    }

    protected function failedValidation(Validator $validator)
    {
        $response = [
            'message' => 'Validation error',
            'errors' => $validator->errors(),
        ];

        throw new HttpResponseException(response()->json($response, 422));
    }

    public function bankList(Request $request, BankService $BankService){
 
        $data = $request->all();
        $selectedApi = 'SAFEHAVEN';
        $BankService = app()->makeWith(BankService::class, ['selectedApi' => $selectedApi]);
        $banklist = $BankService->bankList();
        return $banklist;

    }
    
    public function nameEnquiry(Request $request, BankService $BankService){
        $validatedData = $request->validate([
            'accountNo' => 'required|string|max:255',
            'bankCode' => 'required|string',
        ]);

        $data = $request->all();
        $selectedApi = 'VFD';
        if($data["bankCode"] =="9999A"){
            $selectedApi ="PAYSNUG";
        }
        $BankService = app()->makeWith(BankService::class, ['selectedApi' => $selectedApi]);
        $enquiry = $BankService->nameEnquiry($data);
        return $enquiry;
        
    }
}

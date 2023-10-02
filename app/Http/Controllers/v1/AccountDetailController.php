<?php

namespace App\Http\Controllers\v1;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Wallet;
use Illuminate\Http\Request;
use App\Models\AccountDetail;
use App\Models\Department;
use App\Interfaces\BankService;
use App\Models\Providers;
use App\Http\Requests\AccountDetailRequest;
use App\Services\ResponseHandler;

class AccountDetailController extends Controller
{
    public function createAccount(AccountDetailRequest $request, BankService $BankService)
    {
        $data = $request->validated();
        $dept_id = $request->attributes->get('dept_id');
        $dept = Department::where('id', $dept_id)->first();
        $wallet = Wallet::where('dept_id', $dept_id)
        ->where('id', $data["wallet_id"])
        ->where('status', 1)
        ->first();
        $selectedApi = 'PAYSNUG';
        if($data["is_intra"] === 0 ){
            $provider = Providers::where('status', 1)
            ->where('id', $data["provider_id"])
            ->first();
            if (!$provider) {
                return response()->json(['status'=>98,'message' => 'Provider Not Found'], 422);
            }

            $selectedApi = $provider->provider_name;

        }       
        if (!$wallet) {
            return response()->json(['status'=>98,'message' => 'Wallet Not Found'], 422);
        }

        
        $data['dept_id'] = $dept_id;
        $BankService = app()->makeWith(BankService::class, ['selectedApi' => $selectedApi]);
        $createaccount = $BankService->createAccountIndividual($data);
        if(!$createaccount){
            return response()->json(['status'=>422,'message' => 'Unable to Create Account at the moment',$createaccount], 422); 
        }


        

        $accountdetails = AccountDetail::where('account_number', $createaccount["account_number"])->first();

        if($accountdetails){
            return response()->json(['status'=>98,'message' => 'Account Aleady exist'], 422);
        }
        $data['wallet_id'] = $wallet->id;
        $data['dept_id'] = $wallet->dept_id;
        $data['currency_code'] = $wallet->currency;
        $data['country_id'] = $wallet->country_id;
        $data['country'] = $wallet->country;
        $data['account_type'] = "Individual";
        $data['provider_name'] =  $createaccount["provider_name"];
        $data['account_number'] = $createaccount["account_number"];
        $data['provider_code'] =  $createaccount["provider_code"];
        $data['dept_type'] =  $createaccount["is_app"];
        $data['account_holder_name'] =  $createaccount["account_name"];

       $created =  AccountDetail::create($data);

       $responseHandler = app(ResponseHandler::class);
        $formattedResponse = $responseHandler->accountCreation($created);

        return $formattedResponse;
    }

    public function showByWalletId($walletId)
    {
        // Retrieve account details based on wallet_id
        $accountDetails = AccountDetail::where('wallet_id', $walletId)->get();

        return response()->json(['data' => $accountDetails],201);
    }

    public function showByAccountNumber($accountNumber)
    {
        // Retrieve account details based on account_number
        $accountDetail = AccountDetail::find($accountNumber);

        if (!$accountDetail) {
            return response()->json(['status'=>404,'message' => 'Account details not found'], 404);
        }

        return response()->json(['data' => $accountDetail]);
    }
}

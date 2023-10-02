<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountDetailRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'wallet_id' => 'required',
            'account_holder_name' => 'required',
            'phone_number' => 'required',
            'email' => 'required',
            'is_intra' => ['required', 'in:1,0'],
            'is_fixed' => 'required',
            'provider_id' => [
                'required_if:is_intra,0',
            ],
            'amount' => [
                'required_if:is_fixed,1',
            ],
        ];
        
        
    }


    
}

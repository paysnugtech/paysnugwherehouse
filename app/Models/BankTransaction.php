<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class BankTransaction extends Model
{
    use HasUuids;
    protected $primaryKey = 'id'; // Assuming your primary key is 'id'
    public $incrementing = false; // To use UUIDs as primary keys
    protected $keyType = 'string';// Specify the UUID data type
    protected $guarded = ['id'];
    protected $table = 'bank_transactions';

    public function updateTransactionData($sessionId,$provider_ref,$response_code,$wherehouse_response,$status)
    {
        $this->session_id = $sessionId;
        $this->provider_ref = $provider_ref;
        $this->response_code = $wherehouse_response;
        $this->provider_response = $response_code;
        $this->status = $status;
        $this->save();
    }

    
}


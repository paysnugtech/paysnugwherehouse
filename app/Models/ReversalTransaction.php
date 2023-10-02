<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ReversalTransaction extends Model
{
    use HasUuids;
    protected $primaryKey = 'id'; // Assuming your primary key is 'id'
    public $incrementing = false; // To use UUIDs as primary keys
    protected $keyType = 'string';// Specify the UUID data type
    protected $guarded = ['id'];
    protected $table = 'reversal_transactions';

    // public function updateTransactionData($sessionId,$provider_ref,$response_code,$status)
    // {
    //     $this->session_id = $sessionId;
    //     $this->provider_ref = $provider_ref;
    //     $this->response_code = $response_code;
    //     $this->status = $status;
    //     $this->save();
    // }

    
}


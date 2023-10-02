<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AccountDetail extends Model
{
    use HasFactory;
    use HasUuids;
    protected $primaryKey = 'id'; // Assuming your primary key is 'id'
    public $incrementing = false; // To use UUIDs as primary keys
    protected $keyType = 'string';// Specify the UUID data type
    protected $guarded = ['id'];
    protected $table = 'account_details';

    // protected $fillable = [
    //     'dept_id',
    //     'wallet_id',
    //     'account_number',
    //     'account_holder_name',
    //     'provider_code',
    //     'account_type',
    //     'provider_name',
    //     'status',
    //     'expired_at',
    //     'phone_number',
    //     'email',
    //     'is_intra',
    // ];

    // Define relationships if needed
}

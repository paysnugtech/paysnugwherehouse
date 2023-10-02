<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;
    use HasUuids;
    protected $primaryKey = 'id'; // Assuming your primary key is 'id'
    public $incrementing = false; // To use UUIDs as primary keys
    protected $keyType = 'string';// Specify the UUID data type
    protected $table = 'wallets';
    protected $fillable = [
        'id',
        'dept_id',
        'balance',
        'legal_balance',
        'currency',
        'country_id',
        'country',
        'country_code',
        'currency_code'
    ];
    protected $casts = [
        'status' => 'boolean',
    ];
}

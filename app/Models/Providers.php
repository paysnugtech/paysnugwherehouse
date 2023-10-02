<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Providers extends Model
{
    use HasFactory;
    use HasUuids;
    protected $primaryKey = 'id'; // Assuming your primary key is 'id'
    public $incrementing = false; // To use UUIDs as primary keys
    protected $keyType = 'string';// Specify the UUID data type
    protected $guarded = ['id'];
    protected $table = 'providers';
    protected $casts = [
        'status' => 'boolean',
    ];

}

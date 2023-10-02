<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class DeptToken extends Model
{
    use HasFactory;
    //use HasUuids;

    protected $table = 'dept_tokens';

    protected $fillable = [
        'public_key',
        'secret_key',
        'dept_id'
    ];
}


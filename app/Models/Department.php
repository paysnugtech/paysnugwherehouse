<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

use Illuminate\Support\Str;

class Department extends Model
{
	use HasUuids;
    protected $primaryKey = 'id'; // Assuming your primary key is 'id'
    public $incrementing = false; // To use UUIDs as primary keys
    protected $keyType = 'string';// Specify the UUID data type
    protected $table = 'department';
    protected $fillable = [
    	'id',
        'dept_name',
        'description',
        'email',
        'dept_type',

    ];

    // Define the relationship to dept_tokens
    public function deptToken()
    {
        return $this->hasOne(DeptToken::class);
    }
}

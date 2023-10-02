<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class IpWhitelist extends Model
{
    use HasFactory;
    use HasUuids;
    protected $fillable = ['dept_id', 'ip_address'];
    
    // Define the relationship with the Department model
    public function department()
    {
        return $this->belongsTo(Department::class, 'dept_id', 'id');
    }
}

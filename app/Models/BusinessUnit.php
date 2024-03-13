<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessUnit extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['client_id', 'group_name', 'unit_name', 'function_performed', 'contact_phone'];

    public function businessProcesses()
    {
        return $this->hasMany(BusinessProcess::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessUnit extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['client_id', 'group_name', 'unit_name', 'teams', 'function_performed', 'contact_phone', 'access_code', 'prepend_risk_no_value'];

    public function businessProcesses()
    {
        return $this->hasMany(BusinessProcess::class);
    }
    public function teamMembers()
    {
        return $this->hasMany(OtherUnitsUser::class, 'business_unit_id', 'id');
    }
}

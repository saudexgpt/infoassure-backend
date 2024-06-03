<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RiskRegister extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['client_id', 'business_unit_id', 'risk_id', 'risk_type', 'vunerability_description', 'threat_impact_description', 'existing_controls', 'risk_owner'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function businessUnit()
    {
        return $this->belongsTo(BusinessUnit::class);
    }
}

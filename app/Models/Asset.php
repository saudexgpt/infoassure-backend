<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'client_id', 'asset_type_id'];
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }
    public function riskAssessments()
    {
        return $this->hasMany(RiskAssessment::class);
    }
    public function riskRegisters()
    {
        return $this->hasMany(RiskRegister::class);
    }
}

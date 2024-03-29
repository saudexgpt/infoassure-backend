<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessImpactAnalysis extends Model
{
    use HasFactory, SoftDeletes;
    public function impacts()
    {
        return $this->hasMany(ProcessDisruptionImpact::class, 'business_impact_analysis_id', 'id');
    }
    public function businessUnit()
    {
        return $this->belongsTo(BusinessUnit::class);
    }
    public function businessProcess()
    {
        return $this->belongsTo(BusinessProcess::class);
    }
}

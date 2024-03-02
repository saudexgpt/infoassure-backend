<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessImpactAnalysis extends Model
{
    use HasFactory, SoftDeletes;
    public function processDisruptionImpact()
    {
        return $this->hasMany(ProcessDisruptionImpact::class, 'business_impact_analysis_id', 'id');
    }
}

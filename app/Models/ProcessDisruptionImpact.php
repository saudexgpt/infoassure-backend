<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcessDisruptionImpact extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['client_id', 'business_impact_analysis_id', 'disaster', 'one_hr', 'three_hrs', 'one_day', 'three_days', 'one_week', 'two_weeks'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessDisruptionImpact extends Model
{
    use HasFactory;
    protected $fillable = ['client_id', 'business_impact_analysis_id', 'time_elapse_from_disaster', 'one_hr', 'three_hrs', 'one_day', 'three_days', 'one_week', 'two_weeks'];
}

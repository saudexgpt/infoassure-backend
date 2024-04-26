<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessUnitImpactCriteria extends Model
{
    use HasFactory;
    protected $table = "business_unit_impact_criteria";
    protected $fillable = ['client_id', 'business_unit_id', 'name'];
}

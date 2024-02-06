<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectPhase extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'title', 'partner_id'
    ];
    public function generalProjectPlans()
    {
        return $this->hasMany(GeneralProjectPlan::class);
    }
    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }
}

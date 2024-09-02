<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiaTimeRecoveryRequirement extends Model
{
    use HasFactory;
    protected $table = "bia_time_recovery_requirements";
    protected $fillable = ['client_id', 'name', 'time_in_minutes'];


}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BIARiskAssessment extends Model
{
    use HasFactory;
    public function businessProcess()
    {
        return $this->belongsTo(BusinessProcess::class, 'business_process_id', 'id');
    }
}

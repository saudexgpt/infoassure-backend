<?php

namespace App\Models\ISMS;

use Illuminate\Database\Eloquent\Model;

class Clause extends Model
{
    //
    protected $connection = 'isms';
    protected $fillable = ['sort_by', 'name', 'description'];
    public function responseMonitors()
    {
        return $this->hasMany(ComplianceResponseMonitor::class, 'clause_id', 'id');
    }
    public function questions()
    {
        return $this->hasMany(ComplianceQuestion::class, 'clause_id', 'id');
    }
}

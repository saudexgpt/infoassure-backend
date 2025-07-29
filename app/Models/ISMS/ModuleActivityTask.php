<?php

namespace App\Models\ISMS;

use Illuminate\Database\Eloquent\Model;

class ModuleActivityTask extends Model
{
    protected $connection = 'isms';
    protected $fillable = [
        'clause_id',
        'module_activity_id',
        'dependency',
        'name',
        'description',
        'hint',
        'priority',
        'occurence'
    ];
    public function clause()
    {
        return $this->belongsTo(Clause::class);
    }
    public function activity()
    {
        return $this->belongsTo(ModuleActivity::class, 'module_activity_id', 'id');
    }

    public function assignedTask()
    {
        return $this->hasOne(AssignedTask::class, 'module_activity_task_id', 'id');
    }


}

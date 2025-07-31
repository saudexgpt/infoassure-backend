<?php

namespace App\Models\NDPA;

use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AssignedTask extends Model
{
    protected $connection = 'ndpa';
    protected $fillable = [
        'client_id',
        'clause_id',
        'module_activity_id',
        'module_activity_task_id',
        'assignee_id',
        'days',
        'start_date',
        'end_date',
        'progress',
        'status',
        'assigned_by'
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function clause()
    {
        return $this->belongsTo(Clause::class);
    }
    public function activity()
    {
        return $this->belongsTo(ModuleActivity::class, 'module_activity_id', 'id');
    }
    public function task()
    {
        return $this->belongsTo(ModuleActivityTask::class, 'module_activity_task_id', 'id');
    }
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id', 'id');
    }
    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by', 'id');
    }

}

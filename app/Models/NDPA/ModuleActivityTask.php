<?php

namespace App\Models\NDPA;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class ModuleActivityTask extends Model
{
    protected $connection = 'ndpa';
    protected $fillable = [
        'clause_id',
        'module_activity_id',
        'document_template_ids',
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

    protected function documentTemplateIds(): Attribute
    {
        return Attribute::make(
            get: fn($value) => json_decode($value, true),
            set: fn($value) => json_encode($value),
        );
    }



}

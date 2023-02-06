<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'client_id', 'consulting_id', 'standard_id', 'year'
    ];
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function standard()
    {
        return $this->belongsTo(Standard::class);
    }
    public function consulting()
    {
        return $this->belongsTo(Consulting::class);
    }
    public function certificate()
    {
        return $this->hasOne(ProjectCertificate::class, 'project_id', 'id');
    }

    public function watchProjectProgress(Project $project)
    {
        $project_id  = $project->id;
        $uploaded_documents = Upload::where(['project_id' => $project_id, 'is_exception' => 0])->where('link', '!=', NULL)->count();
        $expected_documents = Upload::where(['project_id' => $project_id])->count();
        $answered_questions = Answer::where(['project_id' => $project_id, 'is_exception' => 0])->where('is_submitted', 1)->count();
        $all_questions = Answer::where(['project_id' => $project_id])->count();

        $total_task = $expected_documents + $all_questions;
        $total_response = $uploaded_documents + $answered_questions;
        $percentage_progress = 0;
        if ($total_task > 0) {
            $percentage_progress = $total_response / $total_task * 100;
        }

        $project->progress = $percentage_progress;
        $project->save();
    }
}

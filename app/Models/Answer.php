<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;
    protected $fillable = ['client_id', 'standard_id', 'project_id', 'consulting_id', 'question_id', 'clause_id', 'created_by'];
    /**
     * Get the user that owns the Answer
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function clause()
    {
        return $this->belongsTo(Clause::class);
    }
    public function standard()
    {
        return $this->belongsTo(Standard::class);
    }
    public function question()
    {
        return $this->belongsTo(Question::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param   $data: array of field to be populated and their values
     * @return void
     */
    public function createProjectAnswer($data)
    {
        Answer::firstOrCreate($data);
    }
}

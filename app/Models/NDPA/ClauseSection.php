<?php

namespace App\Models\NDPA;

use Illuminate\Database\Eloquent\Model;

class ClauseSection extends Model
{
    protected $connection = 'ndpa';
    protected $fillable = [
        'name',
        'description',
        'clause_id'
    ];

    public function clause()
    {
        return $this->belongsTo(Clause::class);
    }
    public function questions()
    {
        return $this->hasMany(Question::class, 'section_id', 'id');
    }
}

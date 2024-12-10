<?php

namespace App\Models\NDPA;

use App\Models\Standard;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Clause extends Model
{
    use HasFactory, SoftDeletes;
    protected $connection = 'ndpa';
    protected $fillable = [
        'name',
        'description',
        'standard_id',
        'will_have_audit_questions',
        'requires_document_upload'
    ];
    public function standard()
    {
        return $this->belongsTo(Standard::class);
    }
    public function questions()
    {
        return $this->hasMany(Question::class);
    }
    // public function uploads()
    // {
    //     return $this->hasMany(Upload::class);
    // }
    public function sections()
    {
        return $this->hasMany(ClauseSection::class);
    }
}

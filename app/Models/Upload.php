<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;
    protected $fillable = ['client_id', 'standard_id', 'project_id', 'consulting_id', 'clause_id', 'created_by'];
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
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}

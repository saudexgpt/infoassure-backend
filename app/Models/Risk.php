<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Risk extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['client_id', 'risk_unique_id', 'type', 'description', 'outcome'];
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}

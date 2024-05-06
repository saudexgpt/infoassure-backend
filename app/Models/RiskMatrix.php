<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskMatrix extends Model
{
    use HasFactory;
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatementOfApplicability extends Model
{
    use HasFactory;
    protected $fillable = ['client_id', 's_o_a_area_id', 's_o_a_control_id'];
    public function client()
    {
        return $this->belongsTo(Client::class, 'client', 'id');
    }
    public function area()
    {
        return $this->belongsTo(SOAArea::class, 's_o_a_area_id', 'id');
    }
    public function control()
    {
        return $this->belongsTo(SOAControl::class, 's_o_a_control_id', 'id');
    }
}

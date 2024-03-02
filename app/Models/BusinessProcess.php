<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessProcess extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['client_id', 'business_unit_id', 'name', 'description', 'roles_responsible', 'no_of_people_involved', 'minimum_no_of_people_involved'];
}

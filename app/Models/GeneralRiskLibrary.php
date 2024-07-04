<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralRiskLibrary extends Model
{
    use HasFactory;
    protected $fillable = ['threats', 'vulnerabilities', 'solutions'];
}

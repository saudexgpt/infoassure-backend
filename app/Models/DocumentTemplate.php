<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = ['title', 'link', 'external_link'];


    // public function getFullLinkAttribute()
    // {
    //     return env('APP_URL') . '/storage/' . $this->link;
    // }
}

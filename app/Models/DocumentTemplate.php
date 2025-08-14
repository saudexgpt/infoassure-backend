<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class DocumentTemplate extends Model
{
    use HasFactory;
    protected $connection = 'mysql';
    protected $fillable = ['title', 'link', 'external_link', 'applicable_modules'];

    protected function applicableModules(): Attribute
    {
        return Attribute::make(
            get: fn($value) => json_decode($value, true),
            set: fn($value) => json_encode($value),
        );
    }


    // public function getFullLinkAttribute()
    // {
    //     return env('APP_URL') . '/storage/' . $this->link;
    // }
}

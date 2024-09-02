<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AvailableModule extends Model
{
    use SoftDeletes;
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    public function activatedModules()
    {
        return $this->hasMany(ActivatedModule::class);
    }
    public function standards()
    {
        return $this->hasMany(Standard::class);
    }
}

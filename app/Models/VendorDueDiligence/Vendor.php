<?php

namespace App\Models\VendorDueDiligence;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Vendor extends Model
{
    use HasFactory, SoftDeletes, Notifiable;
    protected $connection = 'vdd';
    /**
     * The roles that belong to the Client
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}

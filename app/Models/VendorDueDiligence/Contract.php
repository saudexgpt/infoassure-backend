<?php

namespace App\Models\VendorDueDiligence;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;
class Contract extends Model
{
    use SoftDeletes;
    //
    protected $connection = 'vdd';
    protected $fillable = ['client_id', 'vendor_id', 'title', 'file_link', 'start_date', 'expiry_date'];
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function sla()
    {
        return $this->hasOne(SlaConfig::class, 'contract_id', 'id');
    }

    public function score()
    {
        return $this->hasOne(VendorPerformanceScorecard::class, 'contract_id', 'id');
    }
}

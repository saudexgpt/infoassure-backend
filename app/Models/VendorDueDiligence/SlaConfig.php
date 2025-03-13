<?php

namespace App\Models\VendorDueDiligence;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;
class SlaConfig extends Model
{
    use SoftDeletes;
    //
    protected $connection = 'vdd';
    protected $fillable = ['contract_id', 'client_id', 'vendor_id', 'service_name', 'service_description', 'performance_metrics', 'uptime_guarantee', 'response_time', 'resolution_time', 'vendor_responsibilities', 'client_responsibilities', 'report_frequency', 'performance_monitoring_method', 'penalty_type', 'penalty_amount', 'start_date', 'expiry_date', 'renewal_terms', 'approval_workflow'];
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }
    protected function performanceMetrics(): Attribute
    {
        return Attribute::make(
            get: fn($value) => json_decode($value, true),
            set: fn($value) => json_encode($value),
        );
    }
}

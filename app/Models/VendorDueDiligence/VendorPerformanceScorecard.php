<?php

namespace App\Models\VendorDueDiligence;

use App\Models\Client;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
class VendorPerformanceScorecard extends Model
{
    //
    protected $connection = 'vdd';
    protected $fillable = ['contract_id', 'client_id', 'vendor_id', 'sla_config_id', 'kpi_metrics', 'service_quality_rating', 'sla_compliance_status', 'overall_performance_score', 'action_required', 'comments', 'approval_status', 'start_date', 'end_date'];
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

    public function sla()
    {
        return $this->belongsTo(SlaConfig::class, 'sla_config_id', 'id');
    }

    protected function kpiMetrics(): Attribute
    {
        return Attribute::make(
            get: fn($value) => json_decode($value, true),
            set: fn($value) => json_encode($value),
        );
    }
}

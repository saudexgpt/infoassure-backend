<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BusinessProcess extends Model
{

    use HasFactory, SoftDeletes;
    protected $fillable = ['client_id', 'business_unit_id', 'name', 'description', 'roles_responsible', 'no_of_people_involved', 'minimum_no_of_people_involved', 'product_or_service_delivered', 'regulatory_obligations', 'applications_used', 'business_units_depended_on', 'processes_depended_on', 'key_vendors_or_external_dependencies', 'vital_non_electronic_records', 'vital_electronic_records', 'alternative_workaround_during_system_failure', 'key_individuals_process_depends_on', 'peak_periods', 'remote_working'];

    public function businessUnit()
    {
        return $this->belongsTo(BusinessUnit::class);
    }
}

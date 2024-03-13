<?php

namespace App\Http\Controllers;

use App\Models\BusinessProcess;
use App\Models\BusinessUnit;
use App\Models\BusinessImpactAnalysis;
use App\Models\ProcessDisruptionImpact;
use Illuminate\Http\Request;

class BIAController extends Controller
{
    public function fetchBusinessUnits(Request $request)
    {
        $client_id = $request->client_id;
        $business_units = BusinessUnit::with('businessProcesses')->where('client_id', $client_id)->get();
        return response()->json(compact('business_units'), 200);
    }
    public function fetchBusinessProcesses(Request $request)
    {
        $client_id = $request->client_id;
        $business_processes = BusinessProcess::where('client_id', $client_id)->get();
        return response()->json(compact('business_processes'), 200);
    }
    public function saveBusinessUnits(Request $request)
    {
        $client_id = $request->client_id;
        $business_units = json_decode(json_encode($request->business_units));
        foreach ($business_units as $business_unit) {
            BusinessUnit::firstOrCreate([
                'client_id' => $client_id,
                'group_name' => $business_unit->group_name,
                'unit_name' => $business_unit->unit_name,
                'function_performed' => $business_unit->function_performed,
                'contact_phone' => $business_unit->contact_phone
            ]);
        }
        return response()->json(['message' => 'Successful'], 200);
    }
    public function saveBusinessProcesses(Request $request)
    {
        $client_id = $request->client_id;
        $business_processes = json_decode(json_encode($request->business_processes));
        foreach ($business_processes as $business_process) {
            BusinessProcess::firstOrCreate([
                'client_id' => $client_id,
                'business_unit_id' => $business_process->business_unit_id,
                'name' => $business_process->name,
                'description' => $business_process->description,
                'roles_responsible' => $business_process->roles_responsible,
                'no_of_people_involved' => $business_process->no_of_people_involved,
                'minimum_no_of_people_involved' => $business_process->minimum_no_of_people_involved,
                'product_or_service_delivered' => $business_process->product_or_service_delivered,
                'regulatory_obligations' => $business_process->regulatory_obligations,
                'applications_used' => $business_process->applications_used,
                'business_units_depended_on' => $business_process->business_units_depended_on,
                'processes_depended_on' => $business_process->processes_depended_on,
                'key_vendors_or_external_dependencies' => $business_process->key_vendors_or_external_dependencies,
                'vital_non_electronic_records' => $business_process->vital_non_electronic_records,
                'vital_electronic_records' => $business_process->vital_electronic_records,
                'alternative_workaround_during_system_failure' => $business_process->alternative_workaround_during_system_failure,
                'key_individuals_process_depends_on' => $business_process->key_individuals_process_depends_on,
                'peak_periods' => $business_process->peak_periods,
                'remote_working' => $business_process->remote_working,

            ]);
        }
        return response()->json(['message' => 'Successful'], 200);
    }

    public function updateBusinessUnit(Request $request, BusinessUnit $unit)
    {
        $unit->group_name = $request->group_name;
        $unit->unit_name = $request->unit_name;
        $unit->function_performed = $request->function_performed;
        $unit->contact_phone = $request->contact_phone;
        $unit->save();
        return response()->json(compact('unit'), 200);
    }
    public function updateBusinessProcess(Request $request, BusinessProcess $process)
    {
        $process->name = $request->name;
        $process->description = $request->description;
        $process->roles_responsible = $request->roles_responsible;
        $process->no_of_people_involved = $request->no_of_people_involved;
        $process->minimum_no_of_people_involved = $request->minimum_no_of_people_involved;
        $process->save();
        return response()->json(compact('process'), 200);
    }
    public function fetchBIA(Request $request)
    {
        $client_id = $request->client_id;
        // $standard_id = $request->standard_id;
        $business_impact_analyses = BusinessImpactAnalysis::with('businessUnit', 'businessProcess', 'impacts')
            ->where(['client_id' => $client_id/*, 'standard_id' => $standard_id*/])
            ->get(); //->paginate(10);
        return response()->json(compact('business_impact_analyses'), 200);
    }
    public function store(Request $request)
    {
        //
        $client_id = $request->client_id;
        $standard_id = $request->standard_id;
        $business_unit_id = $request->business_unit_id;
        $business_process_id = $request->business_process_id;


        $new_entry = BusinessImpactAnalysis::where([
            'client_id' => $client_id,
            'business_unit_id' => $business_unit_id,
            'business_process_id' => $business_process_id,
        ])->first();
        if (!$new_entry) {
            $new_entry = new BusinessImpactAnalysis();
        }
        $new_entry->client_id = $client_id;
        $new_entry->standard_id = $standard_id;
        $new_entry->business_unit_id = $business_unit_id;
        $new_entry->business_process_id = $business_process_id;
        $new_entry->save();
        $request->business_impact_analysis_id = $new_entry->id;
        $this->createProcessDisruptionImpact($request);
    }
    public function createProcessDisruptionImpact(Request $request)
    {
        $client_id = $request->client_id;
        $process_disruption_impacts = json_decode(json_encode($request->process_disruption_impacts));
        foreach ($process_disruption_impacts as $impact) {
            ProcessDisruptionImpact::firstOrCreate([
                'client_id' => $client_id,
                'business_impact_analysis_id' => $request->business_impact_analysis_id,
                'disaster' => $impact->disaster,
                'one_hr' => $impact->one_hr,
                'three_hrs' => $impact->three_hrs,
                'one_day' => $impact->one_day,
                'three_days' => $impact->three_days,
                'one_week' => $impact->one_week,
                'two_weeks' => $impact->two_weeks
            ]);
        }
    }
    public function update(Request $request, BusinessImpactAnalysis $bia)
    {
        $field = $request->field;
        $value = $request->value;

        $bia->$field = $value;
        $bia->save();
    }
}

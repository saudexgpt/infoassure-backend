<?php

namespace App\Http\Controllers;

use App\Models\BusinessProcess;
use App\Models\BusinessUnit;
use App\Models\BusinessUnitImpactCriteria;
use App\Models\OtherUnitsUser;
use Illuminate\Http\Request;

class BusinessUnitsController extends Controller
{
    //
    public function fetchBusinessUnits(Request $request)
    {
        $client_id = $request->client_id;
        $business_units = BusinessUnit::with('teamMembers')->where('client_id', $client_id)->get();
        return response()->json(compact('business_units'), 200);
    }
    public function fetchBusinessProcesses(Request $request)
    {
        $business_unit_id = $request->business_unit_id;
        $business_processes = BusinessProcess::with('owner')->where('business_unit_id', $business_unit_id)->get();
        return response()->json(compact('business_processes'), 200);
    }
    public function saveBusinessUnits(Request $request)
    {
        $client_id = $request->client_id;
        $business_units = json_decode(json_encode($request->business_units));
        foreach ($business_units as $business_unit) {
            $business_unit = BusinessUnit::firstOrCreate([
                'client_id' => $client_id,
                'group_name' => $business_unit->group_name,
                'unit_name' => $business_unit->unit_name,
                'teams' => $business_unit->teams,
                'function_performed' => $business_unit->function_performed,
                'contact_phone' => $business_unit->contact_phone,
                'access_code' => randomcode()
            ]);

            // create default impact criteria
            $default_impact_criteria = defaultImpactCriteria();
            foreach ($default_impact_criteria as $criteria) {
                BusinessUnitImpactCriteria::firstOrCreate([
                    'client_id' => $client_id,
                    'business_unit_id' => $business_unit->id,
                    'name' => $criteria,
                ]);
            }
        }
        return response()->json(['message' => 'Successful'], 200);
    }
    public function saveBusinessProcesses(Request $request)
    {
        $client_id = BusinessUnit::find($request->business_unit_id)->client_id;
        BusinessProcess::firstOrCreate([
            'client_id' => $client_id,
            'business_unit_id' => $request->business_unit_id,
            'name' => $request->name,
            'process_owner' => $request->process_owner,
            'objective' => $request->name,
            'description' => $request->description,
            'roles_responsible' => $request->roles_responsible,
            'no_of_people_involved' => $request->no_of_people_involved,

            'minimum_no_of_people_involved' => $request->minimum_no_of_people_involved,

            'product_or_service_delivered' => $request->product_or_service_delivered,
            'regulatory_obligations' => $request->regulatory_obligations,
            'applications_used' => $request->applications_used,
            'teams' => implode(',', $request->teams),
            'business_units_depended_on' => implode(',', $request->business_units_depended_on),

            'processes_depended_on' => $request->processes_depended_on,

            'key_vendors_or_external_dependencies' => $request->key_vendors_or_external_dependencies,

            'vital_non_electronic_records' => $request->vital_non_electronic_records,
            'vital_electronic_records' => $request->vital_electronic_records,

            'alternative_workaround_during_system_failure' => $request->alternative_workaround_during_system_failure,

            'key_individuals_process_depends_on' => $request->key_individuals_process_depends_on,

            'peak_periods' => $request->peak_periods,
            'remote_working' => $request->remote_working,

        ]);
        return response()->json(['message' => 'Successful'], 200);
    }

    public function updateBusinessUnit(Request $request, BusinessUnit $unit)
    {
        $unit->group_name = $request->group_name;
        $unit->unit_name = $request->unit_name;
        $unit->function_performed = $request->function_performed;
        $unit->teams = $request->teams;
        $unit->contact_phone = $request->contact_phone;
        $unit->save();
        return response()->json(compact('unit'), 200);
    }
    public function updateBusinessProcess(Request $request, BusinessProcess $process)
    {
        $process->update([
            'name' => $request->name,
            'description' => $request->description,
            'roles_responsible' => $request->roles_responsible,
            'no_of_people_involved' => $request->no_of_people_involved,

            'minimum_no_of_people_involved' => $request->minimum_no_of_people_involved,

            'product_or_service_delivered' => $request->product_or_service_delivered,
            'regulatory_obligations' => $request->regulatory_obligations,
            'applications_used' => $request->applications_used,

            'business_units_depended_on' => implode(',', $request->business_units_depended_on),

            'processes_depended_on' => $request->processes_depended_on,

            'key_vendors_or_external_dependencies' => $request->key_vendors_or_external_dependencies,

            'vital_non_electronic_records' => $request->vital_non_electronic_records,
            'vital_electronic_records' => $request->vital_electronic_records,

            'alternative_workaround_during_system_failure' => $request->alternative_workaround_during_system_failure,

            'key_individuals_process_depends_on' => $request->key_individuals_process_depends_on,

            'peak_periods' => $request->peak_periods,
            'remote_working' => $request->remote_working,

        ]);
        // $process->name = $request->name;
        // $process->description = $request->description;
        // $process->roles_responsible = $request->roles_responsible;
        // $process->no_of_people_involved = $request->no_of_people_involved;
        // $process->minimum_no_of_people_involved = $request->minimum_no_of_people_involved;
        // $process->save();
        return response()->json(compact('process'), 200);
    }
    public function refreshAccessCode(Request $request, BusinessUnit $business_unit)
    {
        $business_unit->access_code = randomcode();
        $business_unit->save();
        return response()->json(compact('business_unit'), 200);

    }
    public function fetchOtherUsers(Request $request)
    {
        $users = OtherUnitsUser::with('businessUnit')->where('business_unit_id', $request->business_unit_id)->get();
        return response()->json(compact('users'), 200);
    }
    public function saveOtherUser(Request $request)
    {
        OtherUnitsUser::firstOrCreate([
            'client_id' => $request->client_id,
            'business_unit_id' => $request->business_unit_id,
            'email' => $request->email,
            'name' => $request->name,
        ]);

        return response()->json('success', 200);
    }
    public function updateOtherUser(Request $request, OtherUnitsUser $user)
    {

        $user->business_unit_id = $request->business_unit_id;
        $user->email = $request->email;
        $user->name = $request->name;
        $user->save();
        return response()->json('success', 200);
    }


    public function getBusinessUnitImpactCriteria(Request $request)
    {
        $client_id = $request->client_id;
        $business_unit_id = $request->business_unit_id;
        $impact_criteria = BusinessUnitImpactCriteria::where([
            'client_id' => $client_id,
            'business_unit_id' => $business_unit_id
        ])->get();
        return response()->json(compact('impact_criteria'), 200);
    }
    public function saveBusinessUnitImpactCriteria(Request $request)
    {
        $client_id = $request->client_id;
        $business_unit_id = $request->business_unit_id;
        $impact_criteria = json_decode(json_encode($request->impact_criteria));
        foreach ($impact_criteria as $criteria) {
            BusinessUnitImpactCriteria::firstOrCreate([
                'client_id' => $client_id,
                'business_unit_id' => $business_unit_id,
                'name' => $criteria,
            ]);
        }
        return response()->json(['message' => 'Successful'], 200);
    }

    public function updateBusinessUnitImpactCriteria(Request $request, BusinessUnitImpactCriteria $criteria)
    {
        $criteria->name = $request->name;
        $criteria->save();
        return response()->json(compact('criteria'), 200);
    }
    public function deleteBusinessUnitImpactCriteria(BusinessUnitImpactCriteria $criteria)
    {
        $criteria->delete();
        return response()->json([], 204);
    }
}

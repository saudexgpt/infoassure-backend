<?php

namespace App\Http\Controllers;

use App\Models\BusinessProcess;
use App\Models\BusinessUnit;
use App\Models\BusinessUnitImpactCriteria;
use App\Models\OtherUnitsUser;
use App\Models\RiskImpactArea;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
                'access_code' => randomcode(),
                'prepend_risk_no_value' => acronym($business_unit->unit_name),
            ]);

            // create default impact criteria
            // $default_impact_criteria = defaultImpactCriteria();
            // foreach ($default_impact_criteria as $criteria) {
            //     RiskImpactArea::firstOrCreate([
            //         'client_id' => $client_id,
            //         'business_unit_id' => $business_unit->id,
            //         'area' => $criteria,
            //     ]);
            // }
        }
        return response()->json(['message' => 'Successful'], 200);
    }
    public function saveBusinessProcesses(Request $request)
    {
        $business_unit = BusinessUnit::find($request->business_unit_id);
        BusinessProcess::firstOrCreate([
            'generated_process_id' => $business_unit->next_process_id,
            'client_id' => $business_unit->client_id,
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
        $business_unit->next_process_id += 1;
        $business_unit->save();
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
    public function uploadProcessFlow(Request $request)
    {
        $this->validate($request, [
            'file_uploaded' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);
        // $client = $this->getClient();
        $business_process = BusinessProcess::find($request->id);
        $folder_key = $business_process->client_id;
        if ($request->file('file_uploaded') != null && $request->file('file_uploaded')->isValid()) {
            if ($business_process->flow_chart_diagram !== null) {

                Storage::disk('public')->delete($business_process->flow_chart_diagram);
            }
            $name = $request->file('file_uploaded')->hashName();
            // $file_name = $name . "." . $request->file('file_uploaded')->extension();
            $link = $request->file('file_uploaded')->storeAs('clients/' . $folder_key . '/business-process-flow', $name, 'public');
            $business_process->flow_chart_diagram = $link;
            $business_process->save();
        }
    }
    public function changeProcessStatus(Request $request, BusinessProcess $process)
    {
        $process->status = $request->status;
        $process->save();
        return response()->json([], 204);
    }
}

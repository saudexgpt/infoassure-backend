<?php

namespace App\Http\Controllers;

use App\Models\AssetType;
use App\Models\BusinessUnit;
use App\Models\Risk;
use App\Models\RiskAssessment;
use App\Models\RiskCategory;
use App\Models\RiskImpact;
use App\Models\RiskLikelihood;
use App\Models\RiskMatrix;
use Illuminate\Http\Request;

class RiskAssessmentsController extends Controller
{


    ////////////////////////Manage Risk////////////////////////////
    public function fetchRisks(Request $request)
    {
        $client_id = $request->client_id;
        $business_unit_id = $request->business_unit_id;
        $risks = Risk::where(['client_id' => $client_id, 'business_unit_id' => $business_unit_id])->get();
        return response()->json(compact('risks'), 200);
    }
    /**
     * Save tnew record.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RiskAssessment  $riskAssessment
     * @return \Illuminate\Http\Response
     */
    public function saveRisk(Request $request)
    {
        $business_unit = BusinessUnit::find($request->business_unit_id);
        $client_id = $request->client_id;
        $business_process_id = $request->business_process_id;
        Risk::firstOrCreate([
            'client_id' => $client_id,
            'business_unit_id' => $request->business_unit_id,
            'business_process_id' => $request->business_process_id,
            'risk_unique_id' => $business_unit->prepend_risk_no_value . $business_unit->next_risk_id,
            'type' => $request->type,
            'description' => $request->description,
            'outcome' => $request->outcome,
            'risk_owner' => $request->risk_owner,
            'control_no' => $request->control_no,
            'control_location' => $request->control_location,
            'control_description' => $request->control_description,
            'control_frequency' => $request->control_frequency,
            'control_owner' => $request->control_owner,
            'control_type' => $request->control_type,
            'nature_of_control' => $request->nature_of_control,
            'application_used_for_control' => $request->application_used_for_control,
            'compensating_control' => $request->compensating_control,
            'test_procedures' => $request->test_procedures,
            'sample_size' => $request->sample_size,
            'data_required' => $request->data_required,
            'link_to_evidence' => $request->link_to_evidence,
            'test_conclusion' => $request->test_conclusion,
            'gap_description' => $request->gap_description,
            'tod_improvement_opportunity' => $request->tod_improvement_opportunity,
            'recommendation' => $request->recommendation,
            'responsibility' => $request->responsibility,
            'timeline' => $request->timeline,
            'tod_gap_status' => $request->tod_gap_status
        ]);
        $business_unit->next_risk_id += 1;
        $business_unit->save();
        return response()->json('success');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RiskAssessment  $riskAssessment
     * @return \Illuminate\Http\Response
     */
    public function updateRisk(Request $request, Risk $risk)
    {
        $risk->type = $request->type;
        $risk->description = $request->description;
        $risk->outcome = $request->outcome;
        $risk->save();
        return response()->json(compact('risk'), 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RiskAssessment  $riskAssessment
     * @return \Illuminate\Http\Response
     */
    public function destroyRisk(Risk $risk)
    {
        $risk->delete();
        return response()->json([], 204);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function fetchAssetTypes()
    {
        $asset_types = AssetType::orderBy('name')->get();
        return response()->json(compact('asset_types'), 200);
    }
    public function fetchImpacts(Request $request)
    {
        $impacts = [];
        $client_id = $request->client_id;
        $risk_matrix = RiskMatrix::where('client_id', $client_id)->first();
        if ($risk_matrix) {
            $matrix = $risk_matrix->current_matrix;
            $impacts = RiskImpact::orderBy('value')->where('client_id', $client_id)->where('matrix', $matrix)->get();
        }

        return response()->json(compact('impacts'), 200);
    }
    public function fetchCategories()
    {
        $categories = RiskCategory::orderBy('name')->get();
        return response()->json(compact('categories'), 200);
    }
    public function fetchLikelihoods(Request $request)
    {
        $likelihoods = [];
        $client_id = $request->client_id;
        $risk_matrix = RiskMatrix::where('client_id', $client_id)->first();
        if ($risk_matrix) {
            $matrix = $risk_matrix->current_matrix;
            $likelihoods = RiskLikelihood::orderBy('value')->where('client_id', $client_id)->where('matrix', $matrix)->get();
        }
        return response()->json(compact('likelihoods'), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function saveImpacts(Request $request)
    {
        $names_array = $request->names;
        foreach ($names_array as $name) {
            RiskImpact::firstOrCreate([
                'name' => trim($name)
            ]);
        }
        return response()->json(['message' => 'Successful'], 200);
    }
    public function saveAssetTypes(Request $request)
    {
        $names_array = $request->names;
        foreach ($names_array as $name) {
            AssetType::firstOrCreate([
                'name' => trim($name)
            ]);
        }
        return response()->json(['message' => 'Successful'], 200);
    }
    public function saveCategories(Request $request)
    {
        $names_array = $request->names;
        foreach ($names_array as $name) {
            RiskCategory::firstOrCreate([
                'name' => trim($name)
            ]);
        }
        return response()->json(['message' => 'Successful'], 200);
    }
    public function saveLikelihoods(Request $request)
    {
        $names_array = $request->names;
        foreach ($names_array as $name) {
            RiskLikelihood::firstOrCreate([
                'name' => trim($name)
            ]);
        }
        return response()->json(['message' => 'Successful'], 200);
    }

    public function deleteImpact(RiskImpact $value)
    {
        $value->delete();
        return response()->json([], 204);
    }
    public function deleteAssetType(AssetType $value)
    {
        $value->delete();
        return response()->json([], 204);
    }
    public function deleteCategory(RiskCategory $value)
    {
        $value->delete();
        return response()->json([], 204);
    }
    public function deleteLikelihood(RiskLikelihood $value)
    {
        $value->delete();
        return response()->json([], 204);
    }


    public function fetchRiskAssessments(Request $request)
    {
        $client_id = $request->client_id;
        $standard_id = $request->standard_id;
        $risk_assessments = RiskAssessment::with([
            'assetType'
        ])->where(['client_id' => $client_id, 'standard_id' => $standard_id])->orderBy('id', 'DESC')->get(); //->paginate(10);
        return response()->json(compact('risk_assessments'), 200);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $client_id = $request->client_id; // $this->getClient()->id;
        $standard_id = $request->standard_id;
        // return $request;
        $assessments = json_decode(json_encode($request->assessments));
        $count = RiskAssessment::where(['client_id' => $client_id, 'standard_id' => $standard_id])->orderBy('ra_id', 'DESC')->select('ra_id')->first();
        if ($count) {

            $ra_id = $count->ra_id + 1;
        } else {
            $ra_id = 1;
        }
        foreach ($assessments as $assessment) {
            $asset_type_id = $assessment->asset_type_id;
            $asset = $assessment->asset;
            $risk_owner = $assessment->risk_owner;

            $new_entry = new RiskAssessment();

            $check_for_same_entry = RiskAssessment::where([
                'client_id' => $client_id,
                'standard_id' => $standard_id,
                'asset_type_id' => $asset_type_id,
                'asset' => $asset,
                'risk_owner' => $risk_owner,
            ])->first();
            if ($check_for_same_entry) {
                $new_entry->ra_id = $check_for_same_entry->ra_id;
            } else {
                $new_entry->ra_id = $ra_id;
                $ra_id++;
            }
            $new_entry->client_id = $client_id;
            $new_entry->standard_id = $standard_id;
            $new_entry->asset_type_id = $asset_type_id;
            $new_entry->asset = $asset;
            $new_entry->risk_owner = $risk_owner;
            $new_entry->threat_impact_description = $assessment->threat_impact_description;
            $new_entry->vulnerability_description = $assessment->vulnerability_description;
            $new_entry->existing_controls = $assessment->existing_controls;
            $new_entry->likelihood_justification = $assessment->likelihood_justification;
            $new_entry->risk_likelihood_id = $assessment->risk_likelihood_id;
            $new_entry->confidentiality = $assessment->confidentiality;
            $new_entry->integrity = $assessment->integrity;
            $new_entry->availability = $assessment->availability;

            $valuesArray = [$assessment->confidentiality, $assessment->integrity, $assessment->availability];

            $impact_val = $this->maxValue($valuesArray);
            $risk_value = $assessment->risk_likelihood_id * $impact_val;
            $risk_category = $this->analyzeRiskCategory($risk_value);

            $new_entry->impact_value = $impact_val;
            $new_entry->risk_value = $risk_value;
            $new_entry->risk_category = $risk_category;
            $new_entry->save();
        }
    }

    private function maxValue($arrayNums)
    {
        $max = 0;
        foreach ($arrayNums as $num) {
            if ($num > $max) {
                $max = $num;
            }
        }
        return $max;
    }
    private function analyzeRiskCategory($riskValue, $matrix = '3x3')
    {
        $category = 'Low';
        switch ($matrix) {
            case '5x5':
                if ($riskValue >= 12) {
                    $category = 'High';
                }
                if ($riskValue >= 5 && $riskValue <= 11) {
                    $category = 'Medium';
                }
                break;

            default:
                if ($riskValue >= 6) {
                    $category = 'High';
                }
                if ($riskValue >= 3 && $riskValue <= 5) {
                    $category = 'Medium';
                }
                break;
        }
        return $category;
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RiskAssessment  $riskAssessment
     * @return \Illuminate\Http\Response
     */
    public function updateFields(Request $request, RiskAssessment $riskAssessment)
    {
        //
        $matrix = $request->matrix;
        $field = $request->field;
        $value = $request->value;
        $riskAssessment->$field = $value;
        // $riskAssessment->impact_value = $request->impact_value;
        // $riskAssessment->risk_value = $request->risk_value;
        // $riskAssessment->risk_category = $request->risk_category;
        $riskAssessment->save();
        $this->updateRiskCategory($riskAssessment, $matrix);
        $this->updateReversedRiskCategory($riskAssessment, $matrix);
        return $riskAssessment;
    }


    private function updateRiskCategory($riskAssessment, $matrix)
    {
        $valuesArray = [$riskAssessment->confidentiality, $riskAssessment->integrity, $riskAssessment->availability];

        $impact_val = $this->maxValue($valuesArray);
        $risk_value = $riskAssessment->risk_likelihood_id * $impact_val;
        $risk_category = $this->analyzeRiskCategory($risk_value, $matrix);

        $riskAssessment->impact_value = $impact_val;
        $riskAssessment->risk_value = $risk_value;
        $riskAssessment->risk_category = $risk_category;
        $riskAssessment->save();
    }
    private function updateReversedRiskCategory($riskAssessment, $matrix)
    {
        $valuesArray = [$riskAssessment->reversed_confidentiality, $riskAssessment->reversed_integrity, $riskAssessment->reversed_availability];

        $impact_val = $this->maxValue($valuesArray);
        $risk_value = $riskAssessment->revised_likelihood_id * $impact_val;
        $reversed_risk_category = $this->analyzeRiskCategory($risk_value, $matrix);

        $riskAssessment->revised_impact_value = $impact_val;
        $riskAssessment->revised_risk_value = $risk_value;
        $riskAssessment->revised_risk_category = $reversed_risk_category;
        $riskAssessment->save();
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\RiskAssessment  $riskAssessment
     * @return \Illuminate\Http\Response
     */
    public function edit(RiskAssessment $riskAssessment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\RiskAssessment  $riskAssessment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, RiskAssessment $riskAssessment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\RiskAssessment  $riskAssessment
     * @return \Illuminate\Http\Response
     */
    public function destroy(RiskAssessment $riskAssessment)
    {
        //
    }
}

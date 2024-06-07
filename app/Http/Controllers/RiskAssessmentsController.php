<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetType;
use App\Models\BusinessUnit;
use App\Models\Risk;
use App\Models\RiskAssessment;
use App\Models\RiskCategory;
use App\Models\RiskImpact;
use App\Models\RiskImpactArea;
use App\Models\RiskImpactOnArea;
use App\Models\RiskLikelihood;
use App\Models\RiskMatrix;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Request;

class RiskAssessmentsController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function fetchAssetTypes(Request $request)
    {
        if (isset($request->client_id)) {
            $client_id = $request->client_id;
        } else {
            $client_id = $this->getClient()->id;
        }
        $asset_types = AssetType::where('client_id', $client_id)->orderBy('name')->get();
        return response()->json(compact('asset_types'), 200);
    }
    public function fetchAssets(Request $request)
    {
        if (isset($request->client_id)) {
            $client_id = $request->client_id;
        } else {
            $client_id = $this->getClient()->id;
        }
        $asset_type_id = $request->asset_type_id;
        $assets = Asset::where(['client_id' => $client_id, 'asset_type_id' => $asset_type_id])->orderBy('name')->get();
        return response()->json(compact('assets'), 200);
    }
    public function fetchImpacts(Request $request)
    {
        $impacts = [];
        if (isset($request->client_id)) {
            $client_id = $request->client_id;
        } else {
            $client_id = $this->getClient()->id;
        }
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
        if (isset($request->client_id)) {
            $client_id = $request->client_id;
        } else {
            $client_id = $this->getClient()->id;
        }
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
        if (isset($request->client_id)) {
            $client_id = $request->client_id;
        } else {
            $client_id = $this->getClient()->id;
        }
        $names_array = $request->names;
        foreach ($names_array as $name) {
            AssetType::firstOrCreate([
                'client_id' => $client_id,
                'name' => trim($name)
            ]);
        }
        return response()->json(['message' => 'Successful'], 200);
    }

    public function saveAssets(Request $request)
    {
        if (isset($request->client_id)) {
            $client_id = $request->client_id;
        } else {
            $client_id = $this->getClient()->id;
        }
        $asset_type_id = $request->asset_type_id;
        $names_array = $request->names;
        foreach ($names_array as $name) {
            Asset::firstOrCreate([
                'client_id' => $client_id,
                'asset_type_id' => $asset_type_id,
                'name' => trim($name)
            ]);
        }
        return response()->json(['message' => 'Successful'], 200);
    }
    public function updateAssetType(Request $request, AssetType $asset_type)
    {
        $asset_type->name = $request->name;
        $asset_type->save();
        return response()->json(['message' => 'Successful'], 200);
    }
    public function updateAsset(Request $request, Asset $asset)
    {
        $asset->name = $request->name;
        // $asset->asset_type_id = $request->asset_type_id;
        $asset->save();
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
        if (isset($request->client_id)) {
            $client_id = $request->client_id;
        } else {
            $client_id = $this->getClient()->id;
        }
        $standard_id = 0;
        if (isset($request->standard_id)) {
            $standard_id = $request->standard_id;
        }
        $module = $request->module;
        $risk_matrix = RiskMatrix::where('client_id', $client_id)->first();
        $risk_appetite = null;
        if ($risk_matrix) {

            $risk_appetite = $risk_matrix->risk_appetite;
        }

        $risk_assessments = RiskAssessment::leftJoin('risk_registers', 'risk_registers.id', 'risk_assessments.risk_register_id')
            ->leftJoin('business_units', 'business_units.id', 'risk_assessments.business_unit_id')
            ->leftJoin('business_processes', 'business_processes.id', 'risk_assessments.business_process_id')
            ->leftJoin('asset_types', 'asset_types.id', 'risk_assessments.asset_type_id')
            ->where(['risk_assessments.client_id' => $client_id, 'risk_assessments.standard_id' => $standard_id, 'risk_assessments.module' => $module])
            ->select('risk_assessments.*', 'risk_registers.*', 'risk_assessments.id as id', \DB::raw('CONCAT(prepend_risk_no_value,risk_id) as risk_id'), 'business_processes.name as business_process', 'business_units.unit_name as business_unit', 'asset_types.name as asset_type')
            ->orderBy('risk_id', 'ASC')
            ->get();

        return response()->json(compact('risk_assessments', 'risk_appetite'), 200);
    }
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $module = $request->module;
        //
        if (isset($request->client_id)) {
            $client_id = $request->client_id;
        } else {
            $client_id = $this->getClient()->id;
        }
        $standard_id = $request->standard_id;
        $business_unit_id = $request->business_unit_id;
        // return $request;
        $assessments = json_decode(json_encode($request->assessments));

        $impact_fields = [
            ['name' => 'Confidentiality', 'slug' => 'C', 'impact_value' => ''],
            ['name' => 'Integrity', 'slug' => 'I', 'impact_value' => ''],
            ['name' => 'Availability', 'slug' => 'A', 'impact_value' => ''],
            ['name' => 'Privacy', 'slug' => 'P', 'impact_value' => ''],
        ];
        if ($module == 'rcsa') {
            $impact_fields = [];

            $impact_areas = RiskImpactArea::where(['client_id' => $client_id])->orderBy('area')->get();
            foreach ($impact_areas as $impact_area) {
                $impact_fields[] = [
                    'name' => $impact_area->area,
                    'slug' => $impact_area->area,
                    'impact_value' => ''
                ];
            }
        }
        foreach ($assessments as $assessment) {

            RiskAssessment::firstOrCreate(
                [
                    'module' => $module,
                    'client_id' => $client_id,
                    'standard_id' => $standard_id,
                    'risk_register_id' => $assessment->risk_register_id,
                    'business_unit_id' => $business_unit_id,
                    'business_process_id' => $assessment->business_process_id,
                ],
                ['impact_data' => $impact_fields, 'revised_impact_data' => $impact_fields]
            );
            // $new_entry->client_id = $client_id;
            // $new_entry->standard_id = $standard_id;
            // $new_entry->asset_type_id = $asset_type_id;
            // $new_entry->asset = $asset;
            // $new_entry->risk_owner = $risk_owner;
            // $new_entry->threat_impact_description = $assessment->threat_impact_description;
            // $new_entry->vulnerability_description = $assessment->vulnerability_description;
            // $new_entry->existing_controls = $assessment->existing_controls;
            // $new_entry->likelihood_justification = $assessment->likelihood_justification;
            // $new_entry->risk_likelihood_id = $assessment->risk_likelihood_id;
            // $new_entry->confidentiality = $assessment->confidentiality;
            // $new_entry->integrity = $assessment->integrity;
            // $new_entry->availability = $assessment->availability;

            // $valuesArray = [$assessment->confidentiality, $assessment->integrity, $assessment->availability];

            // $impact_val = $this->maxValue($valuesArray);
            // $risk_value = $assessment->risk_likelihood_id * $impact_val;
            // $risk_category = $this->analyzeRiskCategory($risk_value);

            // $new_entry->impact_value = $impact_val;
            // $new_entry->risk_value = $risk_value;
            // $new_entry->risk_category = $risk_category;
            // $new_entry->save();
        }
        return response()->json(['message' => 'Success'], 200);
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
        $category = NULL;
        switch ($matrix) {
            case '5x5':
                if ($riskValue >= 12) {
                    $category = 'High';
                }
                if ($riskValue >= 5 && $riskValue <= 11) {
                    $category = 'Medium';
                }
                if ($riskValue >= 1 && $riskValue <= 4) {
                    $category = 'Low';
                }
                break;

            default:
                if ($riskValue >= 6) {
                    $category = 'High';
                }
                if ($riskValue >= 3 && $riskValue <= 5) {
                    $category = 'Medium';
                }
                if ($riskValue >= 1 && $riskValue <= 2) {
                    $category = 'Low';
                }
                break;
        }
        return $category;
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\RiskAssessment  $riskAssessment
     * @return \App\Models\RiskAssessment  $riskAssessment
     */
    public function updateRiskAssessmentFields(Request $request, RiskAssessment $riskAssessment)
    {
        $client_id = $riskAssessment->client_id;
        $sub_field = $request->sub_field;
        $risk_matrix = RiskMatrix::with('creator', 'approver')->where('client_id', $client_id)->first();
        $matrix = $risk_matrix->current_matrix;
        $field = $request->field;
        $value = $request->value;
        $new_data = [];
        if ($sub_field != '') {
            $impact_data = $riskAssessment->$field;
            foreach ($impact_data as $data) {
                if ($sub_field == $data['slug']) {
                    $data['impact_value'] = $value;
                }
                $new_data[] = $data;
            }
            $value = $new_data;
        }


        $riskAssessment->$field = $value;
        $riskAssessment->save();
        $this->updateRiskCategory($riskAssessment, $matrix);
        $this->updateImpactRationale($riskAssessment, $matrix);
        $this->updateReversedRiskCategory($riskAssessment, $matrix);
        $this->updateRevisedImpactRationale($riskAssessment, $matrix);
        return $riskAssessment;
    }
    private function updateImpactRationale($riskAssessment, $matrix)
    {

        $impactOnAreas = RiskImpactOnArea::with('impactArea')->where([
            'impact_value' => $riskAssessment->impact_of_occurence,
            'matrix' => $matrix,
            'client_id' => $riskAssessment->client_id
        ])
            ->whereRaw('impact_level IS NOT NULL')
            ->get();
        $rationale = '';
        foreach ($impactOnAreas as $impactOnArea) {
            $area = $impactOnArea->impactArea->area;
            $impact_level = $impactOnArea->impact_level;
            $rationale .= "<li><strong>$area:</strong> $impact_level</li>";
        }
        $riskAssessment->impact_rationale = '<ul>' . $rationale . '</ul>';
        $riskAssessment->save();
    }
    private function updateRevisedImpactRationale($riskAssessment, $matrix)
    {

        $impactOnAreas = RiskImpactOnArea::with('impactArea')->where([
            'impact_value' => $riskAssessment->revised_impact_of_occurence,
            'matrix' => $matrix,
            'client_id' => $riskAssessment->client_id
        ])
            ->whereRaw('impact_level IS NOT NULL')
            ->get();
        $rationale = '';
        foreach ($impactOnAreas as $impactOnArea) {
            $area = $impactOnArea->impactArea->area;
            $impact_level = $impactOnArea->impact_level;
            $rationale .= "<li><strong>$area:</strong> $impact_level</li>";
        }
        $riskAssessment->revised_impact_rationale = '<ul>' . $rationale . '</ul>';
        $riskAssessment->save();
    }
    private function updateRiskCategory($riskAssessment, $matrix)
    {

        $valuesArray = [];
        $impact_data = $riskAssessment->impact_data;
        foreach ($impact_data as $data) {
            $valuesArray[] = ($data['impact_value'] != '') ? $data['impact_value'] : 0;
        }

        $impact_val = $this->maxValue($valuesArray);
        $risk_value = $riskAssessment->likelihood_of_occurence * $impact_val;
        $risk_category = $this->analyzeRiskCategory($risk_value, $matrix);

        $riskAssessment->impact_of_occurence = ($impact_val > 0) ? $impact_val : NULL;
        $riskAssessment->overall_risk_rating = ($risk_value > 0) ? $risk_value : NULL;
        $riskAssessment->risk_category = $risk_category;
        $riskAssessment->save();
    }
    private function updateReversedRiskCategory($riskAssessment, $matrix)
    {
        $valuesArray = [];
        $impact_data = $riskAssessment->revised_impact_data;
        foreach ($impact_data as $data) {
            $valuesArray[] = ($data['impact_value'] != '') ? $data['impact_value'] : 0;
        }

        $impact_val = $this->maxValue($valuesArray);
        $risk_value = $riskAssessment->revised_likelihood_of_occurence * $impact_val;
        $risk_category = $this->analyzeRiskCategory($risk_value, $matrix);

        $riskAssessment->revised_impact_of_occurence = $impact_val;
        $riskAssessment->revised_overall_risk_rating = $risk_value;
        $riskAssessment->revised_risk_category = $risk_category;
        $riskAssessment->save();
    }

    public function updateRiskFields(Request $request, Risk $risk)
    {
        //
        $field = $request->field;
        $value = $request->value;
        $risk->$field = $value;
        $risk->save();
        return $risk;
    }
}

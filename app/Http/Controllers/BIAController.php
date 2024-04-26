<?php

namespace App\Http\Controllers;

use App\Models\BIARiskAssessment;
use App\Models\BusinessImpactAnalysis;
use App\Models\BusinessProcess;
use App\Models\BusinessUnitImpactCriteria;
use App\Models\ProcessDisruptionImpact;
use Illuminate\Http\Request;

class BIAController extends Controller
{

    public function fetchBIA(Request $request)
    {
        $client_id = $request->client_id;
        $business_unit_id = $request->business_unit_id;
        $business_impact_analyses = BusinessImpactAnalysis::with('businessUnit', 'businessProcess', 'impacts')
            ->where(['client_id' => $client_id, 'business_unit_id' => $business_unit_id])
            ->get(); //->paginate(10);
        return response()->json(compact('business_impact_analyses'), 200);
    }
    public function store(Request $request)
    {
        //
        $client_id = $request->client_id;
        $business_unit_id = $request->business_unit_id;

        //check if there is impact criteria set for this Business Unit
        $impact_criteria = BusinessUnitImpactCriteria::where([
            'client_id' => $client_id,
            'business_unit_id' => $business_unit_id
        ])->get();
        if ($impact_criteria->count() < 1) {
            return response()->json(['message' => 'Business Impact Criteria is not set. Kindly inform your Consultant'], 500);
        }

        $business_processes = BusinessProcess::where(['business_unit_id' => $business_unit_id, 'client_id' => $client_id])->get();

        foreach ($business_processes as $process) {

            $business_process_id = $process->id;
            $new_entry = BusinessImpactAnalysis::where([
                'client_id' => $client_id,
                'business_unit_id' => $business_unit_id,
                'business_process_id' => $business_process_id,
            ])->first();
            if (!$new_entry) {
                $new_entry = new BusinessImpactAnalysis();

                $new_entry->client_id = $client_id;
                // $new_entry->standard_id = $standard_id;
                $new_entry->business_unit_id = $business_unit_id;
                $new_entry->business_process_id = $business_process_id;
                $new_entry->save();




                foreach ($impact_criteria as $criteria) {
                    // create first disruption impact
                    ProcessDisruptionImpact::firstOrCreate([
                        'client_id' => $client_id,
                        'business_impact_analysis_id' => $new_entry->id,
                        'criteria' => $criteria->name
                    ]);
                }



            }
        }

        // $request->business_impact_analysis_id = $new_entry->id;
        // $this->createProcessDisruptionImpact($request);
    }
    public function createProcessDisruptionImpact(Request $request)
    {
        $client_id = $request->client_id;
        $process_disruption_impacts = json_decode(json_encode($request->process_disruption_impacts));
        foreach ($process_disruption_impacts as $impact) {
            ProcessDisruptionImpact::firstOrCreate([
                'client_id' => $client_id,
                'business_impact_analysis_id' => $request->business_impact_analysis_id,
                'criteria' => $impact->criteria,
                'one_hr' => $impact->one_hr,
                'three_hrs' => $impact->three_hrs,
                'one_day' => $impact->one_day,
                'three_days' => $impact->three_days,
                'one_week' => $impact->one_week,
                'two_weeks' => $impact->two_weeks
            ]);
        }
    }
    public function updateBIA(Request $request, BusinessImpactAnalysis $bia)
    {
        $field = $request->field;
        $value = $request->value;

        $bia->$field = $value;
        $bia->save();
    }
    public function updateDisruptionImpact(Request $request, ProcessDisruptionImpact $impact)
    {
        $field = $request->field;
        $value = $request->value;

        $impact->$field = $value;
        $impact->save();
    }
    public function fetchRiskAssessments(Request $request)
    {
        $client_id = $request->client_id;
        $business_unit_id = $request->business_unit_id;
        $risk_assessments = BIARiskAssessment::with('businessProcess')->where(['client_id' => $client_id, 'business_unit_id' => $business_unit_id])->orderBy('id', 'DESC')->get(); //->paginate(10);
        return response()->json(compact('risk_assessments'), 200);
    }
    public function storeRiskAssessment(Request $request)
    {
        //
        $client_id = $request->client_id;
        $business_unit_id = $request->business_unit_id;
        // return $request;
        $assessments = json_decode(json_encode($request->assessments));
        $count = BIARiskAssessment::where(['client_id' => $client_id, 'business_unit_id' => $business_unit_id])->orderBy('ra_id', 'DESC')->select('ra_id')->first();
        if ($count) {

            $ra_id = $count->ra_id + 1;
        } else {
            $ra_id = 1;
        }
        foreach ($assessments as $assessment) {
            $business_process_id = $assessment->business_process_id;
            $risk_description = $assessment->risk_description;
            $risk_owner = $assessment->risk_owner;

            $new_entry = new BIARiskAssessment();

            $check_for_same_entry = BIARiskAssessment::where([
                'client_id' => $client_id,
                'business_unit_id' => $business_unit_id,
                'business_process_id' => $business_process_id,
            ])->first();
            if ($check_for_same_entry) {
                $new_entry->ra_id = $check_for_same_entry->ra_id;
            } else {
                $new_entry->ra_id = $ra_id;
                $ra_id++;
            }
            $new_entry->client_id = $client_id;
            $new_entry->business_unit_id = $business_unit_id;
            $new_entry->business_process_id = $business_process_id;
            $new_entry->risk_description = $risk_description;
            $new_entry->risk_owner = $risk_owner;
            $new_entry->risk_description = $assessment->risk_description;
            $new_entry->existing_treatment = $assessment->existing_treatment;
            $new_entry->likelihood_rationale = $assessment->likelihood_rationale;
            $new_entry->likelihood = $assessment->likelihood;
            $new_entry->impact = $assessment->impact;
            $new_entry->impact_rationale = $assessment->impact_rationale;
            // $new_entry->risk_score = $assessment->risk_score;
            // $new_entry->risk_level = $assessment->risk_level;
            $risk_score = $new_entry->likelihood * $new_entry->impact;
            if ($risk_score > 0) {
                $risk_level = $this->analyzeRiskLevel($risk_score);

                $new_entry->risk_score = $risk_score;
                $new_entry->risk_level = $risk_level;
            }
            $new_entry->save();
        }
    }
    public function updateRiskAssessmentFields(Request $request, BIARiskAssessment $assessment)
    {
        //
        $matrix = $request->matrix;
        $field = $request->field;
        $value = $request->value;
        $assessment->$field = $value;
        $assessment->save();

        $this->updateRiskCategory($assessment, $matrix);
        $this->updateReversedRiskCategory($assessment, $matrix);
        return $assessment;
    }
    private function updateRiskCategory($riskAssessment, $matrix)
    {
        $risk_score = $riskAssessment->likelihood * $riskAssessment->impact;
        if ($risk_score > 0) {

            $risk_level = $this->analyzeRiskLevel($risk_score);

            $riskAssessment->risk_score = $risk_score;
            $riskAssessment->risk_level = $risk_level;
            $riskAssessment->save();
        }
    }
    private function updateReversedRiskCategory($riskAssessment, $matrix)
    {
        $risk_score = $riskAssessment->post_treatment_likelihood * $riskAssessment->post_treatment_impact;
        if ($risk_score > 0) {
            $risk_level = $this->analyzeRiskLevel($risk_score);

            $riskAssessment->post_treatment_risk_score = $risk_score;
            $riskAssessment->post_treatment_risk_level = $risk_level;
            $riskAssessment->save();
        }
    }
    private function analyzeRiskLevel($riskValue, $matrix = '3x3')
    {
        $category = 'Low';
        if ($riskValue >= 6) {
            $category = 'High';
        }
        if ($riskValue >= 3 && $riskValue <= 5) {
            $category = 'Medium';
        }
        // switch ($matrix) {
        //     case '5x5':
        //         if ($riskValue >= 12) {
        //             $category = 'High';
        //         }
        //         if ($riskValue >= 5) {
        //             $category = 'Medium';
        //         }
        //         break;

        //     default:
        //         if ($riskValue >= 6) {
        //             $category = 'High';
        //         }
        //         if ($riskValue >= 3) {
        //             $category = 'Medium';
        //         }
        //         break;
        // }
        return $category;
    }
    public function riskAssessmentSummary(Request $request)
    {
        $client_id = $request->client_id;
        $business_unit_id = $request->business_unit_id;
        $summary = BIARiskAssessment::join('business_processes', 'business_processes.id', '=', 'b_i_a_risk_assessments.business_process_id')
            ->groupBy('business_process_id')
            ->where(['b_i_a_risk_assessments.client_id' => $client_id, 'b_i_a_risk_assessments.business_unit_id' => $business_unit_id])
            ->select('business_processes.name as business_process_name', 'risk_owner', \DB::raw('COUNT(*) as no_of_threats'), \DB::raw('COUNT(CASE WHEN risk_level = "Low" THEN b_i_a_risk_assessments.id END ) as lows'), \DB::raw('COUNT(CASE WHEN risk_level = "Medium" THEN b_i_a_risk_assessments.id END ) as mediums'), \DB::raw('COUNT(CASE WHEN risk_level = "High" THEN b_i_a_risk_assessments.id END ) as highs'))
            ->get();
        return response()->json(compact('summary'), 200);
    }
}
